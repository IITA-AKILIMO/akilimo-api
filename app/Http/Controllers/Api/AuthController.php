<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Models\PersonalAccessToken;
use App\Service\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->attempt(
            $request->input('username'),
            $request->input('password'),
        );

        if ($result === null) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = $result['user'];

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $result['token'],
            'expires_at' => $result['expires_at']->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('auth_token');

        if ($token instanceof PersonalAccessToken) {
            $this->authService->revoke($token);
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
