<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ManpowerAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Allow users with is_admin = true OR can_view_manpower = true.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        if (!$user->is_admin && !$user->can_view_manpower) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Manpower access required.',
            ], 403);
        }

        return $next($request);
    }
}