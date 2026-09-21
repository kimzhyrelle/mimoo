<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSession
{
    /**
     * Handle an incoming request.
     *
     * Allows access only if the session flag 'admin_verified' is set to true.
     * This flag is written by the AdminController after the master code is verified.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_verified', false)) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
