<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RoleLevel;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionMatrixController extends Controller
{
    use ApiResponseTrait;

    /**
     * Ambil semua permission (dikelompokkan) + semua role level,
     * plus matrix assignment saat ini (role_level_id => [permission_id, ...]).
     */
    public function index(): JsonResponse
    {
        try {
            $permissions = Permission::orderBy('group')->orderBy('id')->get();
            $roles = RoleLevel::with('permissions:id')->orderBy('name')->get();

            $matrix = $roles->mapWithKeys(function ($role) {
                return [$role->id => $role->permissions->pluck('id')];
            });

            return $this->successResponse([
                'permissions' => $permissions->groupBy('group'),
                'roles'       => $roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name]),
                'matrix'      => $matrix,
            ], 'Permission matrix retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update matrix — full replace untuk satu role sekaligus.
     * Body: { "role_level_id": 2, "permission_ids": [1,2,5,7] }
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_level_id'    => 'required|exists:role_levels,id',
            'permission_ids'   => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $role = RoleLevel::findOrFail($validated['role_level_id']);
                $role->permissions()->sync($validated['permission_ids'] ?? []);
            });

            return $this->successResponse(null, 'Permission matrix updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}