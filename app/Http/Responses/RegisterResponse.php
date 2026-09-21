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
     * Buyers are auto-approved and redirected to /dashboard.
     * Sellers are logged out and redirected to /register/pending since
     * their account needs admin approval.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // If seller, log out and send to pending page
        if ($user && $user->account_type === 'seller') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('register.pending');
        }

        // Buyers go to the homepage (buyer shopping interface)
        return redirect()->route('homepage');
    }
}
