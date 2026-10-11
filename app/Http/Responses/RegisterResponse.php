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
     * All new accounts (both buyers and sellers) start as pending and require
     * admin approval. After registration, users are logged out and redirected
     * to the pending confirmation page.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        // Immediately logout the newly registered user since they need approval
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to pending page
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->route('register.pending')->with('message', 'Your registration is pending approval.');
    }
}
