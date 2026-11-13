<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Middleware;

use App\Auth\TokenAbility;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional sign-in for routes that stay open to everyone (POST /v1/recommendations/compute).
 *
 * A valid farmer token (with the compute ability, from a farmer who has given consent) marks the request as the farmer's:
 * the user is set on the request and its id is kept at attribute "farmer_id", so the saved recommendation is linked to them.
 * Anything else, including no credentials, a staff token, an API key or an expired token, is treated as anonymous and
 * passes through unchanged: existing callers (the app, the MCP server, partners) must keep working. An advice computed
 * with an expired token is still saved under the phone's device_token and is claimed at the next sign-in.
 */
class OptionalFarmerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $raw = $request->bearerToken();

        if (filled($raw)) {
            $token = PersonalAccessToken::where('token', hash('sha256', $raw))->with('tokenable')->first();
            $user = $token?->tokenable;

            if ($token !== null
                && $token->name === RequireFarmerToken::TOKEN_NAME
                && ($token->expires_at === null || !$token->expires_at->isPast())
                && $token->can(TokenAbility::RECOMMENDATIONS_COMPUTE)
                && $user instanceof User
                && $user->consent_at !== null
            ) {
                $request->setUserResolver(fn () => $user);
                $request->attributes->set('farmer_id', $user->id);
            }
        }

        return $next($request);
    }
}
