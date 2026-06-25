<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a paginated listing of users with optional filters & search.
     *
     * Query params:
     *   search         — matches name, email, npk, username
     *   department_id  — filter by department
     *   section_id     — filter by section
     *   role_level_id  — filter by role level
     *   is_admin       — filter by admin flag (true/false)
     *   per_page       — items per page (default 15)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['department', 'section', 'roleLevel', 'director'])
                ->orderBy('name');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            if ($request->filled('role_level_id')) {
                $query->where('role_level_id', $request->role_level_id);
            }

            if ($request->has('is_admin')) {
                $query->where('is_admin', filter_var($request->is_admin, FILTER_VALIDATE_BOOLEAN));
            }

            $users = $query->paginate($request->per_page ?? 15);

            return $this->successResponse(
                UserResource::collection($users)->response()->getData(true),
                'Users retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'npk'           => $request->npk,
                'name'          => $request->name,
                'username'      => $request->username,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'department_id' => $request->department_id,
                'section_id'    => $request->section_id,
                'role_level_id' => $request->role_level_id,
                'director_id'   => $request->director_id,
                'is_admin'      => $request->boolean('is_admin', false),
            ]);

            $user->load(['department', 'section', 'roleLevel', 'director']);

            return $this->successResponse(
                new UserResource($user),
                'User berhasil dibuat.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user->load(['department', 'section', 'roleLevel', 'director']);

            return $this->successResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $user->update($request->only([
                'npk',
                'name',
                'username',
                'email',
                'department_id',
                'section_id',
                'role_level_id',
                'director_id',
                'is_admin',
            ]));

            $user->load(['department', 'section', 'roleLevel', 'director']);

            return $this->successResponse(
                new UserResource($user),
                'User berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified user.
     * Prevents admin from deleting their own account.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            if (auth()->id() === $user->id) {
                return $this->errorResponse('Tidak dapat menghapus akun sendiri.', 403);
            }

            $user->delete();

            return $this->successResponse(null, 'User berhasil dihapus.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Reset a user's password by admin.
     */
    public function resetPassword(ResetPasswordRequest $request, User $user): JsonResponse
    {
        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return $this->successResponse(
                null,
                "Password user {$user->name} berhasil direset."
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}