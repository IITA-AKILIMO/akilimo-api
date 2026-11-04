<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
    }

    private function configureRateLimiters(): void
    {
        // Every limit comes from config/rate-limits.php (read on each request); each key is a limiter used by the routes
        // as "throttle:<key>", with one counter per IP shared by the routes that use the same key
        foreach (array_keys(config('rate-limits')) as $name) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute((int) config("rate-limits.$name"))->by($name.'|'.$request->ip()));
        }

        // Admin sign-in uses the "sensitive" limit but redirects back with a field error on breach
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute((int) config('rate-limits.sensitive'))
                ->by('admin-login|'.$request->ip())
                ->response(fn () => back()
                    ->withErrors(['username' => 'Too many login attempts. Please wait a minute and try again.'])
                    ->onlyInput('username')
                );
        });
    }
}