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
        $isVerified = $request->session()->get('admin_verified', false);
        
        // Debug logging
        \Log::info('Admin middleware check', [
            'path' => $request->path(),
            'admin_verified' => $isVerified,
            'session_id' => $request->session()->getId(),
        ]);
        
        if (!$isVerified) {
            // Return JSON error for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized admin access'], 403);
            }
            
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
