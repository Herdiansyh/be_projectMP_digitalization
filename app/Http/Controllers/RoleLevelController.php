<?php

namespace App\Http\Controllers;

use App\Models\RoleLevel;
use Illuminate\Http\JsonResponse;

class RoleLevelController extends Controller
{
    /**
     * Publik — dipakai untuk isi dropdown role di halaman login,
     * sebelum user terautentikasi.
     */
    public function index(): JsonResponse
    {
        $roles = RoleLevel::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }
}