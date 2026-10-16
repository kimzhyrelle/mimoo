<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification page
     */
    public function showVerification(Request $request): Response
    {
        $email = $request->query('email');
        
        return Inertia::render('auth/VerifyEmail', [
            'email' => $email,
        ]);
    }
    
    /**
     * Verify the OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'code.size' => 'Verification code must be exactly 6 digits.',
            'code.regex' => 'Verification code must be exactly 6 digits.',
        ]);
        
        // Debug logging
        \Log::info('OTP VERIFICATION ATTEMPT', [
            'email' => $request->email,
            'code' => $request->code,
            'code_length' => strlen($request->code),
        ]);
        
        $verification = EmailVerification::findValid($request->email, $request->code);
        
        if (!$verification) {
            // Debug: check what codes exist for this email
            $allCodes = EmailVerification::where('email', $request->email)
                ->orderBy('created_at', 'desc')
                ->get(['code', 'verified', 'expires_at']);
            
            \Log::info('NO VALID CODE FOUND', [
                'email' => $request->email,
                'submitted_code' => $request->code,
                'existing_codes' => $allCodes->map(fn($c) => [
                    'code' => $c->code,
                    'verified' => $c->verified,
                    'expired' => $c->isExpired(),
                    'expires_at' => $c->expires_at,
                ])->toArray(),
            ]);
            
            // Check if there's an expired code
            $expired = EmailVerification::where('email', $request->email)
                ->where('code', $request->code)
                ->where('verified', false)
                ->first();
            
            if ($expired && $expired->isExpired()) {
                return back()->withErrors([
                    'code' => 'Code expired. Please request a new code.',
                ]);
            }
            
            return back()->withErrors([
                'code' => 'Invalid verification code.',
            ]);
        }
        
        \Log::info('OTP VERIFICATION SUCCESS', [
            'email' => $request->email,
            'code' => $request->code,
        ]);
        
        // Mark verification as used
        $verification->update(['verified' => true]);
        
        // Update user status to verified
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $user->update([
                'verification_status' => 'verified',
                'email_verified_at' => now(),
            ]);
        }
        
        // Redirect to login with success message
        return redirect()->route('login')->with('status', 'Email verified successfully! You can now log in.');
    }
    
    /**
     * Resend verification code
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        // Check if already verified
        if ($user->verification_status === 'verified') {
            return back()->withErrors([
                'email' => 'This email is already verified.',
            ]);
        }
        
        // Create new OTP
        $verification = EmailVerification::createForEmail($request->email);
        
        // Send OTP email
        Notification::route('mail', $request->email)
            ->notify(new EmailVerificationNotification($verification->code));
        
        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
