<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRiderApprovalAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TODO(admin-auth): sign-in is currently disabled. When roles return,
        // require $request->user()?->role === 'admin' here and remove the
        // SORTING_REQUIRE_ADMIN_FOR_RIDER_APPROVAL escape hatch.
        if (config('sorting.require_admin_for_rider_approval')) {
            $user = $request->user();

            if ($user === null || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
                abort(403, 'Only administrators can approve or reject rider applications.');
            }
        }

        return $next($request);
    }
}
