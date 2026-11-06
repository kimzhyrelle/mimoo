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
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        
        // Check if email is verified
        if ($user->verification_status === 'unverified') {
            auth()->logout();
            return redirect()->route('otp.show', ['email' => $user->email])
                ->withErrors(['email' => 'Please verify your email address before logging in.']);
        }

        // Check if account is approved (for sellers and logistics)
        if (in_array($user->account_type, ['seller', 'logistics']) && $user->status === 'pending') {
            auth()->logout();
            return redirect()->route('register.pending')
                ->with('message', 'Your account is pending approval. You will receive an email once approved.');
        }

        // Check if account was rejected
        if (in_array($user->account_type, ['seller', 'logistics']) && $user->status === 'rejected') {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account application was rejected. Please contact support for more information.']);
        }

        // DEBUG: Show me exactly what's happening
        \Log::info('LOGIN REDIRECT DEBUG', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'account_type' => $user->account_type,
            'status' => $user->status,
        ]);

        // Redirect based on account type
        $home = match ($user->account_type) {
            'seller' => '/seller/dashboard',
            'logistics' => '/logistics/dashboard',
            default => '/homepage',
        };

        \Log::info('REDIRECTING TO: ' . $home);

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect($home);
    }
}
