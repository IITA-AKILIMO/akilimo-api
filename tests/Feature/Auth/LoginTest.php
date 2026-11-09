<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createTestUser(string $password = 'secret123'): User
{
    return User::create([
        'name' => 'Auth Test User',
        'username' => 'authuser_' . str()->random(6),
        'email' => 'auth_' . str()->random(6) . '@example.com',
        'password' => Hash::make($password),
    ]);
}

// ── Login ─────────────────────────────────────────────────────────────────────

it('returns a bearer token on valid username + password', function () {
    $user = createTestUser();

    $this->postJson('/api/v1/auth/login', [
        'username' => $user->username,
        'password' => 'secret123',
    ])->assertOk()
        ->assertJsonStructure(['token_type', 'token', 'expires_at', 'user'])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.username', $user->username);
});

it('accepts email as the login identifier', function () {
    $user = createTestUser();

    $this->postJson('/api/v1/auth/login', [
        'username' => $user->email,
        'password' => 'secret123',
    ])->assertOk()
        ->assertJsonPath('user.email', $user->email);
});

it('returns 401 for a wrong password', function () {
    $user = createTestUser();

    $this->postJson('/api/v1/auth/login', [
        'username' => $user->username,
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

it('returns 401 for an unknown username', function () {
    $this->postJson('/api/v1/auth/login', [
        'username' => 'nobody',
        'password' => 'secret123',
    ])->assertUnauthorized();
});

it('returns 422 when username is missing', function () {
    $this->postJson('/api/v1/auth/login', ['password' => 'secret123'])
        ->assertUnprocessable();
});

it('returns 422 when password is missing', function () {
    $user = createTestUser();

    $this->postJson('/api/v1/auth/login', ['username' => $user->username])
        ->assertUnprocessable();
});

it('stores the hashed token in personal_access_tokens', function () {
    $user = createTestUser();

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => $user->username,
        'password' => 'secret123',
    ])->assertOk();

    $rawToken = $response->json('token');
    $hash = hash('sha256', $rawToken);

    expect(PersonalAccessToken::where('token', $hash)->exists())->toBeTrue();
});

// ── Logout ───────────────────────────────────────────────────────────────────

it('returns 200 and deletes the token on logout', function () {
    $user = createTestUser();

    $loginResp = $this->postJson('/api/v1/auth/login', [
        'username' => $user->username,
        'password' => 'secret123',
    ])->assertOk();

    $rawToken = $loginResp->json('token');

    $this->withToken($rawToken)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect(PersonalAccessToken::where('token', hash('sha256', $rawToken))->exists())->toBeFalse();
});

it('returns 401 on logout without a token', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});

it('cannot reuse a token after logout', function () {
    $user = createTestUser();

    $rawToken = $this->postJson('/api/v1/auth/login', [
        'username' => $user->username,
        'password' => 'secret123',
    ])->json('token');

    $this->withToken($rawToken)->postJson('/api/v1/auth/logout')->assertOk();

    $this->withToken($rawToken)
        ->getJson('/api/v1/translations')
        ->assertUnauthorized();
});
