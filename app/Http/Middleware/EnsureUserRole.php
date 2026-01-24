<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in route: ->middleware('role:physio')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user || strtolower($user->role) !== strtolower($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: role not allowed.',
            ], 403);
        }

        return $next($request);
    }
}
