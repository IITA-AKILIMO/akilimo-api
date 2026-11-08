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
