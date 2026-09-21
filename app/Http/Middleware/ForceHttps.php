<?php

/**
 * (c) 2026 Munywele Consulting LTD — https://munywele.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
