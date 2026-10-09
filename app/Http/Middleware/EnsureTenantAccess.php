<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Super Admin bypasses tenant restriction
            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            if (!$user->tenant_id || !$user->tenant) {
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Your user account is not associated with any organization.']);
            }

            if (!$user->tenant->isActive()) {
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Your organization account is suspended or expired. Please contact support.']);
            }

            // Share current tenant with views
            view()->share('currentTenant', $user->tenant);
            view()->share('currentUser', $user);
        }

        return $next($request);
    }
}
