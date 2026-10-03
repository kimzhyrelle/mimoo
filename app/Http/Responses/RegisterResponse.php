<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * All new accounts (both buyers and sellers) start as pending and require
     * admin approval. After registration, users are logged out and redirected
     * to the pending confirmation page.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // All users (buyers and sellers) need approval
        if ($user) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('register.pending');
        }

        // Fallback
        return redirect()->route('home');
    }
}
