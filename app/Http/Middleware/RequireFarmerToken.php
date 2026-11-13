<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Account routes belong to farmers: only a token issued by the farmer sign-in (name "farmer") passes.
 * A staff token or an API key, even with every ability, cannot delete a user or read a person's history here.
 * Use after auth.token.
 */
class RequireFarmerToken
{
    public const TOKEN_NAME = 'farmer';

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->attributes->get('auth_token');

        if (!$token instanceof PersonalAccessToken || $token->name !== self::TOKEN_NAME) {
            return response()->json(['message' => 'Forbidden. This route is for farmer account tokens.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
