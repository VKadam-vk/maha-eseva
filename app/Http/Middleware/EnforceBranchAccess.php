<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceBranchAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isSuperAdmin() || $user->isBusinessOwner()) {
                return $next($request);
            }

            // If user is branch restricted, ensure branch parameter in URL matches user's branch
            $branchId = $request->route('branch') ?? $request->input('branch_id');
            if ($branchId) {
                $branchId = is_object($branchId) ? $branchId->id : (int) $branchId;
                if ($user->branch_id && $user->branch_id !== $branchId) {
                    abort(403, 'Unauthorized: You do not have permission to access records from this branch.');
                }
            }
        }

        return $next($request);
    }
}
