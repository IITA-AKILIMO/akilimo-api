<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The farmer must have given consent before the API returns or links any of their advice (an older account has to
 * answer `consent_required` first). Use after auth.token and farmer.
 */
class RequireConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof User || $user->consent_at === null) {
            return response()->json(['message' => 'Consent is needed first.', 'code' => 'consent_required'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
