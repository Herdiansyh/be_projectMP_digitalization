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
     * Relasi yang di-load untuk detail lengkap user (show, store, update).
     */
    private const FULL_RELATIONS = [
        'department',
        'section',
        'roleLevel',
        'director',
        'approverManager',
        'approverDivision',
        'approverDirector',
    ];

    /**
     * Relasi minimal untuk listing (index) — hindari over-fetching.
     */
    private const LIST_RELATIONS = [
        'department',
        'section',
        'roleLevel',
        'approverManager',
        'approverDivision',
        'approverDirector',
    ];

    /**
     * Relasi untuk approver chain saja.
     */
    private const APPROVER_RELATIONS = [
        'approverManager',
        'approverDivision',
        'approverDirector',
    ];

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    /**
     * Display a paginated listing of users with optional filters & search.
     *
     * Query params:
     *   search         — matches name, email, npk, username
     *   department_id  — filter by department
     *   section_id     — filter by section
     *   role_level_id  — filter by role level
     *   is_admin       — filter by admin flag (true/false)
     *   per_page       — items per page (default 15, max 100)
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        try {
            $perPage = (int) min($request->input('per_page', 10), 100);

            $query = User::with(self::LIST_RELATIONS)->orderBy('name');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name',     'like', "%{$search}%")
                      ->orWhere('email',    'like', "%{$search}%")
                      ->orWhere('npk',      'like', "%{$search}%")
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

            // Gunakan filled() agar string kosong tidak ikut diproses
            if ($request->filled('is_admin')) {
                $query->where('is_admin', filter_var($request->is_admin, FILTER_VALIDATE_BOOLEAN));
            }

            $users = $query->paginate($perPage);

            return $this->successResponse(
                UserResource::collection($users)->response()->getData(true),
                'Users retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        try {
            $user = User::create([
                'npk'                  => $request->npk,
                'name'                 => $request->name,
                'username'             => $request->username,
                'email'                => $request->email,
                'password'             => Hash::make($request->password),
                'department_id'        => $request->department_id,
                'section_id'           => $request->section_id,
                'role_level_id'        => $request->role_level_id,
                'director_id'          => $request->director_id,
                'is_admin'             => $request->boolean('is_admin', false),
                'approver_manager_id'  => $request->approver_manager_id,
                'approver_division_id' => $request->approver_division_id,
                'approver_director_id' => $request->approver_director_id,
            ]);

            $user->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new UserResource($user),
                'User berhasil dibuat.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        try {
            $user->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

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
                'approver_manager_id',
                'approver_division_id',
                'approver_director_id',
            ]));

            $user->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new UserResource($user),
                'User berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    /**
     * Remove the specified user.
     * Prevents user from deleting their own account (juga dijaga di Policy).
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        try {
            // Guard tambahan — defence in depth jika Policy terlewat
            if (auth()->id() === $user->id) {
                return $this->errorResponse('Tidak dapat menghapus akun sendiri.', 403);
            }

            $user->delete();

            return $this->successResponse(null, 'User berhasil dihapus.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // Reset Password
    // -------------------------------------------------------------------------

    /**
     * Reset a user's password by admin.
     */
    public function resetPassword(ResetPasswordRequest $request, User $user): JsonResponse
    {
        $this->authorize('resetPassword', $user);

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

    // -------------------------------------------------------------------------
    // Approvers
    // -------------------------------------------------------------------------

    /**
     * Get the approver chain for the specified user.
     * Response dibungkus UserResource agar tidak expose field sensitif.
     */
    public function getApproversForUser(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        try {
            $user->loadMissing(self::APPROVER_RELATIONS);

            return $this->successResponse([
                'approver_manager'  => $user->approverManager
                    ? new UserResource($user->approverManager)
                    : null,
                'approver_division' => $user->approverDivision
                    ? new UserResource($user->approverDivision)
                    : null,
                'approver_director' => $user->approverDirector
                    ? new UserResource($user->approverDirector)
                    : null,
            ], 'User approvers retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}