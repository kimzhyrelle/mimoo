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

        // DEBUG: Show me exactly what's happening
        \Log::info('LOGIN REDIRECT DEBUG', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'account_type' => $user->account_type,
            'account_type_raw' => $user->getRawOriginal('account_type'),
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
