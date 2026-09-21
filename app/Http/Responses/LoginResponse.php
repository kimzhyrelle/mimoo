<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * Buyers are redirected to /homepage (the shopping interface).
     * Sellers and other account types go to /dashboard.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // Buyers go to the homepage shopping interface
        if ($user && $user->account_type === 'buyer') {
            return redirect()->route('homepage');
        }

        // Everyone else (sellers, etc.) goes to dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }
}
