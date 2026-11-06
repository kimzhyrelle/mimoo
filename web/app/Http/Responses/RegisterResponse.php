<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
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
    public function toResponse($request)
    {
        $user = Auth::user();
        $email = $user?->email;

        // Immediately logout the newly registered user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Use a standard redirect - let the frontend handle it via onSuccess
        return redirect()->route('otp.show', ['email' => $email]);
    }
}
