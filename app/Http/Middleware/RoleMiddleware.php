<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated: Please log in to continue.'
            ], 401);
        }

        if ($user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: You do not have permissions to access this endpoint.'
            ], 403);
        }

        return $next($request);
    }
}
