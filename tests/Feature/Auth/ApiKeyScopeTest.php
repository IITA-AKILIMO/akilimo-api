<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole;
use App\Models\ApiKey;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->withoutVite());

function scopeUser(EnumUserRole $role): User
{
    return User::create([
        'name' => ucfirst($role->value) . ' User',
        'username' => $role->value . '_' . str()->random(6),
        'email' => $role->value . '_' . str()->random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => $role,
    ]);
}

function scopeKey(User $owner, ?array $abilities = null): ApiKey
{
    $raw = 'ak_' . bin2hex(random_bytes(16));

    return $owner->apiKeys()->create([
        'name' => 'key of ' . $owner->username,
        'key_prefix' => substr($raw, 0, 12),
        'key_hash' => hash('sha256', $raw),
        'abilities' => $abilities,
        'is_active' => true,
    ]);
}

// ── Admin area: partners only manage their own keys, with partner abilities ─────

it('lets a partner see only their own keys', function () {
    $partner = scopeUser(EnumUserRole::Partner);
    $other = scopeUser(EnumUserRole::User);
    $own = scopeKey($partner, TokenAbility::PARTNER_ABILITIES);
    scopeKey($other);

    $this->actingAs($partner)->get('/admin/api-keys?search=key')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ApiKeys/Index')
            ->has('apiKeys.data', 1)
            ->where('apiKeys.data.0.id', $own->id));
});

it('offers a partner only themselves and the partner abilities', function () {
    $partner = scopeUser(EnumUserRole::Partner);
    scopeUser(EnumUserRole::Admin);

    $this->actingAs($partner)->get('/admin/api-keys/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ApiKeys/Create')
            ->has('users', 1)
            ->where('users.0.id', $partner->id)
            ->where('canWildcard', false)
            ->where('abilities', fn ($abilities) => !collect($abilities)->contains(TokenAbility::ADMIN)));
});

it('refuses a partner a key for another user', function () {
    $partner = scopeUser(EnumUserRole::Partner);
    $admin = scopeUser(EnumUserRole::Admin);

    $this->actingAs($partner)
        ->post('/admin/api-keys', ['user_id' => $admin->id, 'name' => 'theirs', 'abilities' => [TokenAbility::RECOMMENDATIONS_COMPUTE]])
        ->assertSessionHasErrors('user_id');

    expect(ApiKey::where('user_id', $admin->id)->exists())->toBeFalse();
});

it('refuses a partner admin, wildcard and empty abilities', function (array $abilities) {
    $partner = scopeUser(EnumUserRole::Partner);

    $this->actingAs($partner)
        ->post('/admin/api-keys', ['user_id' => $partner->id, 'name' => 'escalate', 'abilities' => $abilities])
        ->assertSessionHasErrors();

    expect(ApiKey::where('user_id', $partner->id)->exists())->toBeFalse();
})->with([
    'admin' => [[TokenAbility::ADMIN]],
    'wildcard' => [[TokenAbility::WILDCARD]],
    'empty (wildcard)' => [[]],
    'mcp:verify-keys' => [[TokenAbility::MCP_VERIFY_KEYS]],
    'broad write' => [[TokenAbility::WRITE]],
]);

it('lets a partner create their own key with partner and MCP abilities', function () {
    $partner = scopeUser(EnumUserRole::Partner);
    $abilities = [TokenAbility::RECOMMENDATIONS_COMPUTE, TokenAbility::MCP_USE];

    $this->actingAs($partner)
        ->post('/admin/api-keys', ['user_id' => $partner->id, 'name' => 'mine', 'abilities' => $abilities])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('ApiKeys/Created'));

    expect(ApiKey::where('user_id', $partner->id)->first()->abilities)->toBe($abilities);
});

it('answers 404 when a partner touches another user\'s key', function () {
    $partner = scopeUser(EnumUserRole::Partner);
    $key = scopeKey(scopeUser(EnumUserRole::Admin));

    $this->actingAs($partner);
    $this->get("/admin/api-keys/{$key->id}/edit")->assertNotFound();
    $this->put("/admin/api-keys/{$key->id}", ['user_id' => $partner->id, 'name' => 'stolen', 'abilities' => [TokenAbility::RECOMMENDATIONS_COMPUTE]])->assertNotFound();
    $this->patch("/admin/api-keys/{$key->id}/revoke")->assertNotFound();
    $this->patch("/admin/api-keys/{$key->id}/activate")->assertNotFound();
    $this->delete("/admin/api-keys/{$key->id}")->assertNotFound();

    $key->refresh();
    expect($key->user_id)->not->toBe($partner->id)
        ->and($key->is_active)->toBeTrue();
});

it('refuses a partner widening their own key to admin', function () {
    $partner = scopeUser(EnumUserRole::Partner);
    $key = scopeKey($partner, TokenAbility::PARTNER_ABILITIES);

    $this->actingAs($partner)
        ->put("/admin/api-keys/{$key->id}", ['user_id' => $partner->id, 'name' => 'mine', 'abilities' => [TokenAbility::ADMIN]])
        ->assertSessionHasErrors('abilities.0');

    expect($key->refresh()->abilities)->toBe(TokenAbility::PARTNER_ABILITIES);
});

it('still lets an admin create any key for any user', function () {
    $admin = scopeUser(EnumUserRole::Admin);
    $partner = scopeUser(EnumUserRole::Partner);

    $this->actingAs($admin)
        ->post('/admin/api-keys', ['user_id' => $partner->id, 'name' => 'by admin', 'abilities' => [TokenAbility::ADMIN]])
        ->assertOk();

    expect(ApiKey::where('user_id', $partner->id)->first()->abilities)->toBe([TokenAbility::ADMIN]);
});

// ── API: a new key never does more than the key or token that creates it ───────

it('refuses a key that grants more than the caller has', function (array $abilities) {
    $user = scopeUser(EnumUserRole::User);
    $raw = bin2hex(random_bytes(32));
    PersonalAccessToken::create([
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => 'manage only',
        'token' => hash('sha256', $raw),
        'abilities' => [TokenAbility::API_KEYS_MANAGE],
        'expires_at' => now()->addDay(),
    ]);

    $this->withToken($raw)
        ->postJson('/api/v1/auth/api-keys', ['name' => 'escalate', ...($abilities === [] ? [] : ['abilities' => $abilities])])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('abilities');

    expect(ApiKey::where('user_id', $user->id)->exists())->toBeFalse();
})->with([
    'admin' => [[TokenAbility::ADMIN]],
    'no abilities (wildcard)' => [[]],
    'compute' => [[TokenAbility::RECOMMENDATIONS_COMPUTE]],
]);
