<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Answers 404 on every playground URL while config('playground.enabled') is false */
class EnsurePlaygroundEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('playground.enabled'), 404);

        return $next($request);
    }
}
