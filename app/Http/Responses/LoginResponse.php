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
     * Check account approval status first:
     * - pending: logout and redirect to pending page
     * - disapproved: logout and redirect to login with error
     * - approved: proceed with normal flow
     *
     * Approved buyers are redirected to /homepage (the shopping interface).
     * Approved sellers and other account types go to /dashboard.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('home');
        }

        // Check approval status
        if ($user->status === 'pending') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('register.pending')
                ->with('message', 'Your account is still pending approval. You will be notified once approved.');
        }

        if ($user->status === 'disapproved') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('error', 'Your account application was not approved. Please contact support for more information.');
        }

        // Account is approved, proceed with normal flow
        // Buyers go to the homepage shopping interface
        if ($user->account_type === 'buyer') {
            return redirect()->route('homepage');
        }

        // Everyone else (sellers, etc.) goes to dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }
}
