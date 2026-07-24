<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Usage in routes: ->middleware('permission:fptk.approve')
     * Bisa juga multiple, dipisah koma: ->middleware('permission:fptk.approve,fptk.process_hrd')
     * (lolos kalau user punya SALAH SATU dari permission yang disebut)
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $keys = explode(',', $permissions);

        $allowed = collect($keys)->contains(fn ($key) => $user->hasPermission(trim($key)));

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have the required permission.',
            ], 403);
        }

        return $next($request);
    }
}