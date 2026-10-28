<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates requests via one of two mechanisms (tried in order):
 *
 *  1. Bearer token  — Authorization: Bearer <token>
 *     Looked up in personal_access_tokens (SHA-256 hash match).
 *
 *  2. API key       — X-Api-Key: <key>  (or Authorization: Bearer ak_…)
 *     Looked up in api_keys (SHA-256 hash match).
 *     Must be active and not expired.
 *
 * Optional ability requirements can be passed as middleware parameters:
 *
 *   Route::middleware('auth.token:read')           // single ability
 *   Route::middleware('auth.token:read,write')     // must have both
 *
 * On success:
 *  - The resolved User is bound via $request->setUserResolver()
 *  - The resolved token/key is stored at $request->attributes->get('auth_token')
 */
class AuthenticateWithToken
{
    private const API_KEY_PREFIX = 'ak_';

    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $token = $this->resolveToken($request);

        if ($token === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        foreach ($abilities as $ability) {
            if (! $token->can($ability)) {
                return response()->json(
                    ['message' => "Forbidden. This token does not have the [{$ability}] ability."],
                    Response::HTTP_FORBIDDEN,
                );
            }
        }

        $user = $token instanceof PersonalAccessToken
            ? $token->tokenable
            : $token->user;

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_token', $token);

        return $next($request);
    }

    private function resolveToken(Request $request): PersonalAccessToken|ApiKey|null
    {
        return $this->resolveFromBearer($request)
            ?? $this->resolveFromApiKey($request);
    }

    private function resolveFromBearer(Request $request): ?PersonalAccessToken
    {
        $raw = $request->bearerToken();

        if (blank($raw)) {
            return null;
        }

        $token = PersonalAccessToken::where('token', hash('sha256', $raw))
            ->with('tokenable')
            ->first();

        if ($token === null || ($token->expires_at !== null && $token->expires_at->isPast())) {
            return null;
        }

        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        return $token;
    }

    private function resolveFromApiKey(Request $request): ?ApiKey
    {
        $raw = $request->header('X-Api-Key');

        if (blank($raw) && str_starts_with((string) $request->bearerToken(), self::API_KEY_PREFIX)) {
            $raw = $request->bearerToken();
        }

        if (blank($raw)) {
            return null;
        }

        $apiKey = ApiKey::where('key_hash', hash('sha256', $raw))
            ->with('user')
            ->first();

        if ($apiKey === null || ! $apiKey->isUsable()) {
            return null;
        }

        $apiKey->forceFill(['last_used_at' => now()])->saveQuietly();

        return $apiKey;
    }
}
