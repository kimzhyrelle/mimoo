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
        
        // Immediately logout the newly registered user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to email verification page
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->route('otp.show', ['email' => $email])
                ->with('status', 'Registration successful! Please check your email for a verification code.');
    }
}
