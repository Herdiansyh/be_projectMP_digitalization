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
    $user = User::with(['roleLevel', 'department', 'section', 'area'])
        ->where('npk', $credentials['npk'])  // ← ganti dari email ke npk
        ->first();

    if (!$user) {
        throw new Exception('Invalid credentials');
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
        $user = User::with(['roleLevel', 'department', 'section', 'area'])
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
    $oldToken = JWTAuth::getToken();

    // Ambil payload dari token lama SEBELUM di-refresh,
    // karena setelah refresh, token lama otomatis di-blacklist.
    $payload = JWTAuth::setToken($oldToken)->getPayload();
    $userId  = $payload->get('sub');

    $newToken = JWTAuth::refresh($oldToken);

    $user = User::with(['roleLevel', 'department', 'section', 'area'])
        ->find($userId);

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