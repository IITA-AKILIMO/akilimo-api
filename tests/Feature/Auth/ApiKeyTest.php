<?php

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

function apiKeyUser(): User
{
    return User::create([
        'name' => 'Key User',
        'username' => 'keyuser_'.str()->random(6),
        'email' => 'key_'.str()->random(6).'@example.com',
        'password' => Hash::make('secret123'),
    ]);
}

// ── Access control ────────────────────────────────────────────────────────────

it('returns 401 when listing keys without authentication', function () {
    $this->getJson('/api/v1/auth/api-keys')->assertUnauthorized();
});

it('returns 401 when creating a key without authentication', function () {
    $this->postJson('/api/v1/auth/api-keys', ['name' => 'test'])->assertUnauthorized();
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a key and returns the raw key once', function () {
    $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', ['name' => 'My key'])
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'key_prefix', 'abilities', 'key'], 'message'])
        ->assertJsonPath('data.abilities', ['*']);
});

it('stores scoped abilities on the key', function () {
    $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', [
            'name' => 'Read only',
            'abilities' => [TokenAbility::READ],
        ])->assertCreated()
        ->assertJsonPath('data.abilities', [TokenAbility::READ]);

    $key = ApiKey::latest()->first();
    expect($key->abilities)->toBe([TokenAbility::READ]);
});

it('returns 422 for an unrecognised ability', function () {
    $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', [
            'name' => 'Bad key',
            'abilities' => ['not-a-real-ability'],
        ])->assertUnprocessable();
});

it('returns 422 when expires_at is in the past', function () {
    $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', [
            'name' => 'Expired',
            'expires_at' => now()->subDay()->toDateTimeString(),
        ])->assertUnprocessable();
});

// ── Authenticate with the generated key ───────────────────────────────────────

it('can authenticate a protected route with the generated key', function () {
    $rawKey = $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', ['name' => 'auth test'])
        ->json('data.key');

    $this->flushHeaders()
        ->withHeader('X-Api-Key', $rawKey)
        ->getJson('/api/v1/translations')
        ->assertOk();
});

it('can authenticate with the generated key sent as a bearer token', function () {
    $rawKey = $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', ['name' => 'bearer test'])
        ->json('data.key');

    $this->flushHeaders()
        ->withToken($rawKey)
        ->getJson('/api/v1/translations')
        ->assertOk();
});

it('returns 401 for an invalid api key sent as a bearer token', function () {
    $this->withToken('ak_invalidkey')
        ->getJson('/api/v1/translations')
        ->assertUnauthorized();
});

it('returns 401 for an invalid api key', function () {
    $this->withHeader('X-Api-Key', 'ak_invalidkey')
        ->getJson('/api/v1/translations')
        ->assertUnauthorized();
});

// ── List ──────────────────────────────────────────────────────────────────────

it('lists only the authenticated users own keys', function () {
    $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', ['name' => 'key one']);

    $response = $this->getJson('/api/v1/auth/api-keys')->assertOk();

    expect($response->json())->toHaveCount(1);
});

// ── Revoke ────────────────────────────────────────────────────────────────────

it('revoked key returns 401 on subsequent requests', function () {
    $response = $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', ['name' => 'to revoke']);

    $keyId = $response->json('data.id');
    $rawKey = $response->json('data.key');

    $this->patchJson("/api/v1/auth/api-keys/{$keyId}/revoke")->assertOk();

    $this->flushHeaders()
        ->withHeader('X-Api-Key', $rawKey)
        ->getJson('/api/v1/translations')
        ->assertUnauthorized();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deleted key returns 401 on subsequent requests', function () {
    $response = $this->actingAsApiUser()
        ->postJson('/api/v1/auth/api-keys', ['name' => 'to delete']);

    $keyId = $response->json('data.id');
    $rawKey = $response->json('data.key');

    $this->deleteJson("/api/v1/auth/api-keys/{$keyId}")->assertNoContent();

    $this->flushHeaders()
        ->withHeader('X-Api-Key', $rawKey)
        ->getJson('/api/v1/translations')
        ->assertUnauthorized();
});

// ── Admin UI ──────────────────────────────────────────────────────────────────

it('shows the raw key once after an admin creates it, without storing it', function () {
    $admin = apiKeyUser();
    $admin->forceFill(['role' => EnumUserRole::Admin])->save();
    $owner = apiKeyUser();

    $response = $this->actingAs($admin)
        ->post('/admin/api-keys', ['user_id' => $owner->id, 'name' => 'admin made', 'abilities' => []])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ApiKeys/Created')
            ->where('apiKey.name', 'admin made')
            ->has('plainTextKey'));

    $rawKey = $response->viewData('page')['props']['plainTextKey'];
    $key = ApiKey::latest('id')->first();

    expect($rawKey)->toStartWith('ak_')->toHaveLength(35)
        ->and($key->key_hash)->toBe(hash('sha256', $rawKey))
        ->and($key->abilities)->toBeNull()
        ->and(session()->all())->not->toContain($rawKey);

    $this->get('/admin/api-keys')
        ->assertInertia(fn (Assert $page) => $page->component('ApiKeys/Index')->missing('plainTextKey'));

    auth()->logout();
    $this->withToken($rawKey)->getJson('/api/v1/translations')->assertOk();
});

// ── Expiry ────────────────────────────────────────────────────────────────────

it('expired key returns 401', function () {
    $user = apiKeyUser();

    $rawKey = 'ak_'.bin2hex(random_bytes(16));
    $user->apiKeys()->create([
        'name' => 'expired',
        'key_prefix' => substr($rawKey, 0, 12),
        'key_hash' => hash('sha256', $rawKey),
        'abilities' => null,
        'expires_at' => now()->subMinute(),
    ]);

    $this->withHeader('X-Api-Key', $rawKey)
        ->getJson('/api/v1/translations')
        ->assertUnauthorized();
});
