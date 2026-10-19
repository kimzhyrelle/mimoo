<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * All new accounts (both buyers and sellers) start as pending and unverified.
     * After registration, users are logged out and redirected to the email
     * verification page to confirm their email address.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $email = $user ? $user->email : null;
        
        \Log::info('REGISTER RESPONSE DEBUG', [
            'user_id' => $user?->id,
            'user_email' => $email,
            'account_type' => $user?->account_type,
            'wants_json' => $request->wantsJson(),
            'is_inertia' => $request->header('X-Inertia'),
        ]);
        
        // Immediately logout the newly registered user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Log::info('REDIRECTING TO OTP PAGE', [
            'email' => $email,
            'route' => route('otp.show', ['email' => $email]),
        ]);

        // Always redirect for Inertia requests (never return JSON)
        // Inertia handles redirects properly via the X-Inertia-Location header
        return redirect()->route('otp.show', ['email' => $email])
            ->with('status', 'Registration successful! Please check your email for a verification code.');
    }
}
