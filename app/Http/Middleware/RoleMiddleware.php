<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! $user->hasAnyRole($roles) && ! in_array($user->role, $roles))) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized. Insufficient permissions.'
                ], 403);
            }
            
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
