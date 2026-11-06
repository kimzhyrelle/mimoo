<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Rules\StrongPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

/**
 * Mobile buyer password reset using Laravel Cache (CACHE_STORE=database).
 *
 * Cache key: "buyer_reset:{lowercased_email}"
 * Cache value: JSON { "hash": "<Hash::make(code)>", "attempts": 0 }
 * TTL: 5 minutes
 *
 * No new table or column is used.
 * The 6-digit code is never returned in API responses or written to Laravel logs
 * directly; only the mail driver (log driver in dev) writes it.
 */
class BuyerPasswordController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const TTL_MINUTES  = 5;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function cacheKey(string $email): string
    {
        return 'buyer_reset:' . strtolower(trim($email));
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/buyer/forgot-password
    //
    // Always returns 200 with the same message regardless of whether the email
    // exists, so callers cannot enumerate accounts.
    // ─────────────────────────────────────────────────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));

        // Look up buyer — silently do nothing if not found / not a buyer / not verified
        $user = User::where('email', $email)->first();

        if (
            $user &&
            $user->account_type === 'buyer' &&
            $user->verification_status === 'verified'
        ) {
            $code = $this->generateCode();

            // Store hashed code + attempt counter; replaces any existing entry
            Cache::put(
                $this->cacheKey($email),
                ['hash' => Hash::make($code), 'attempts' => 0],
                now()->addMinutes(self::TTL_MINUTES)
            );

            // Send notification — mail driver writes code to log in local env
            try {
                Notification::route('mail', $email)
                    ->notifyNow(new PasswordResetNotification($code));
            } catch (\Throwable $e) {
                \Log::warning('Password reset notification failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Always same 200 response
        return response()->json([
            'message' => 'If that email belongs to a verified buyer account, a reset code has been sent.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/buyer/reset-password
    //
    // Validates code from cache (max 5 wrong attempts), then resets password.
    // On success: clears code, revokes all Sanctum tokens.
    // ─────────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'                 => ['required', 'email'],
            'code'                  => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'password'              => ['required', 'string', new StrongPassword(), 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'code.size'  => 'Reset code must be exactly 6 digits.',
            'code.regex' => 'Reset code must be exactly 6 digits.',
        ]);

        $email = strtolower(trim($request->input('email')));
        $key   = $this->cacheKey($email);
        $entry = Cache::get($key);

        // No entry (expired, never requested, or already used)
        if (! $entry) {
            return response()->json([
                'message' => 'Invalid or expired reset code.',
                'errors'  => ['code' => ['Invalid or expired reset code.']],
            ], 422);
        }

        $attempts = (int) ($entry['attempts'] ?? 0);

        // Max attempts exceeded (entry should have been deleted, but guard anyway)
        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($key);
            return response()->json([
                'message' => 'Invalid or expired reset code.',
                'errors'  => ['code' => ['Invalid or expired reset code.']],
            ], 422);
        }

        // Check the code
        if (! Hash::check($request->input('code'), $entry['hash'])) {
            $attempts++;

            if ($attempts >= self::MAX_ATTEMPTS) {
                // Delete code — must request again
                Cache::forget($key);
            } else {
                // Update attempt counter, keep same TTL remainder
                // Note: Cache::put with remaining TTL is not directly available,
                // so we keep the full 5 min TTL on update (acceptable trade-off)
                Cache::put($key, ['hash' => $entry['hash'], 'attempts' => $attempts],
                    now()->addMinutes(self::TTL_MINUTES));
            }

            return response()->json([
                'message' => 'Invalid or expired reset code.',
                'errors'  => ['code' => ['Invalid or expired reset code.']],
            ], 422);
        }

        // ── Code is correct ───────────────────────────────────────────────────

        // Only update password for a verified buyer account.
        // Generic error if not found to avoid enumeration.
        $user = User::where('email', $email)
            ->where('account_type', 'buyer')
            ->where('verification_status', 'verified')
            ->first();

        if (! $user) {
            Cache::forget($key);
            return response()->json([
                'message' => 'Invalid or expired reset code.',
                'errors'  => ['code' => ['Invalid or expired reset code.']],
            ], 422);
        }

        // Update password (model casts 'password' to hashed)
        $user->update(['password' => $request->input('password')]);

        // Invalidate code (single use)
        Cache::forget($key);

        // Revoke all Sanctum tokens so old sessions are logged out
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successfully. Please log in with your new password.',
        ]);
    }
}
