<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAuthor
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please sign in again.'
            ], 401);
        }

        if ($user->role !== 'author') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Author access only.'
            ], 403);
        }

        return $next($request);
    }
}