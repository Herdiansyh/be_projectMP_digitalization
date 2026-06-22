<?php

namespace App\Services;

use App\Http\Resources\AuthUserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Login user.
     */
    public function login(array $credentials): array
    {
        $user = User::with('role')
            ->where('email', $credentials['email'])
            ->first();

        if (!$user) {
            throw new Exception('Invalid credentials');
        }

        if (!$user->is_active) {
            throw new Exception('Account is inactive');
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw new Exception('Invalid credentials');
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->buildAuthResponse($token, $user);
    }

    /**
     * Logout user.
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Get authenticated user.
     */
    public function me(): AuthUserResource
    {
        $user = User::with('role')
            ->find(Auth::id());

        if (!$user) {
            throw new Exception('User not authenticated');
        }

        return new AuthUserResource($user);
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): array
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        $user = User::with('role')
            ->find(Auth::id());

        if (!$user) {
            throw new Exception('User not authenticated');
        }

        return $this->buildAuthResponse($newToken, $user);
    }

    /**
     * Build authentication response.
     */
    private function buildAuthResponse(string $token, User $user): array
    {
        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => new AuthUserResource($user),
        ];
    }
}