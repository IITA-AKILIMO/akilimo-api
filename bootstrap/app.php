<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateWithToken;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\JsonFormatResponse;
use App\Http\Middleware\OptionalFarmerAuth;
use App\Http\Middleware\RequireConsent;
use App\Http\Middleware\RequireFarmerToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->prepend(HandleCors::class);
        $middleware->prepend(ForceHttps::class);

        $middleware->web(
            append: [
                HandleInertiaRequests::class,
            ]
        );

        $middleware->api(
            prepend: [
                JsonFormatResponse::class,
            ]
        );

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.token' => AuthenticateWithToken::class,
            'role' => EnsureAdminRole::class,
            'farmer' => RequireFarmerToken::class,
            'farmer.consented' => RequireConsent::class,
            'farmer.optional' => OptionalFarmerAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();
    })->create();
