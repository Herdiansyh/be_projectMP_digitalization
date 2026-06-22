<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login user and return token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
            return $this->successResponse($result, 'Login successful');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    /**
     * Logout user and invalidate token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout();
            return $this->successResponse(null, 'Logout successful');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->me();
            return $this->successResponse($user, 'User retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    /**
     * Refresh JWT token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refresh();
            return $this->successResponse($result, 'Token refreshed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }
}
