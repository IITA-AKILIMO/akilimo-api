<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Tests;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * fertilizer_prices, cassava_prices and potato_prices are database views, and
     * migrate:fresh leaves views behind unless asked to drop them, which makes the
     * next run's schema load fail with "table already exists".
     */
    protected bool $dropViews = true;

    protected function setUp(): void
    {
        parent::setUp();

        // No test loads the built front-end assets, so Vite is stood in for: a page renders without `public/build`, and CI needs
        // no Node install or `pnpm build` to run the tests (without this, a page test fails with "Vite manifest not found").
        $this->withoutVite();
    }

    /**
     * Create a test user with a wildcard bearer token and apply it to all
     * subsequent requests in this test. Call in beforeEach() or at the start
     * of any test that hits a protected route.
     */
    protected function actingAsApiUser(): static
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser_' . str()->random(8),
            'email' => 'test_' . str()->random(8) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $rawToken = bin2hex(random_bytes(32));

        PersonalAccessToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'test',
            'token' => hash('sha256', $rawToken),
            'abilities' => ['*'],
            'expires_at' => now()->addDay(),
        ]);

        return $this->withToken($rawToken);
    }
}
