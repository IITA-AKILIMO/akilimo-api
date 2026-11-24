<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Providers;

use App\Models\User;
use App\Support\ViewRebuilder;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiters();

        // Register security schemes on the generated OpenAPI instance
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer'));
            $openApi->secure(SecurityScheme::apiKey('X-API-Key', 'header'));
        });

        Gate::define('viewApiDocs', fn (?User $user) => true);

        $this->rebuildViewsAfterMigrations();
    }

    /**
     * A view follows a table that gained or lost a column only when it is made again, so every migration run
     * ends by creating the views again from database/views (the same as `php artisan views:rebuild`). It waits
     * until the views have their vw_ names (the migration that renames them is the first run that makes any),
     * and a view that fails to rebuild is logged and left as it was, so a migration never fails for it.
     */
    private function rebuildViewsAfterMigrations(): void
    {
        Event::listen(MigrationsEnded::class, function () {
            $views = app(ViewRebuilder::class);

            if (! $views->hasPrefixedViews()) {
                return;
            }

            foreach ($views->rebuild()['failed'] as $name => $error) {
                Log::warning("The view {$name} could not be rebuilt after the migrations and was left as it was. Fix database/views/{$name}.sql and run php artisan views:rebuild.", ['error' => $error]);
            }
        });
    }

    private function configureRateLimiters(): void
    {
        // Every limit comes from config/rate-limits.php (read on each request); each key is a limiter used by the routes
        // as "throttle:<key>", with one counter per IP shared by the routes that use the same key
        foreach (array_keys(config('rate-limits')) as $name) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute((int)config("rate-limits.{$name}"))->by($name . '|' . $request->ip()));
        }

        // Admin sign-in uses the "sensitive" limit but redirects back with a field error on breach
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute((int)config('rate-limits.sensitive'))
                ->by('admin-login|' . $request->ip())
                ->response(fn () => back()
                    ->withErrors(['username' => 'Too many login attempts. Please wait a minute and try again.'])
                    ->onlyInput('username')
                );
        });
    }
}
