<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Middleware;

use App\Enums\EnumUserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = array_filter(array_map(fn ($r) => EnumUserRole::tryFrom($r), $roles));

        if ($user === null || !in_array($user->role, $allowed, true)) {
            return redirect()->route('admin.login')->withErrors([
                'username' => 'You do not have permission to access this area.',
            ]);
        }

        return $next($request);
    }
}
