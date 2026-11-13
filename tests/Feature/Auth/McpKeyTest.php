<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole;
use App\Models\ApiKey;
use App\Models\User;
use App\Service\ApiKeyService;
use Illuminate\Support\Facades\Hash;

function mcpKeyUser(): User
{
    return User::create([
        'name' => 'MCP User',
        'username' => 'mcpuser_' . str()->random(6),
        'email' => 'mcp_' . str()->random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => EnumUserRole::User,
    ]);
}

/** A raw key with the given abilities, as the MCP server or a user would hold it. */
function mcpRawKey(array $abilities): string
{
    return app(ApiKeyService::class)->issue(mcpKeyUser(), 'test', $abilities)['raw'];
}

// ── Creating MCP keys in the playground ───────────────────────────────────────

it('creates an MCP key with only mcp:use from the playground', function () {
    $this->actingAs(mcpKeyUser())
        ->postJson('/playground/tokens', ['name' => 'Claude Desktop', 'type' => 'mcp'])
        ->assertCreated()
        ->assertJsonPath('abilities', [TokenAbility::MCP_USE]);

    expect(ApiKey::latest('id')->first()->abilities)->toBe([TokenAbility::MCP_USE]);
});

it('still creates an ordinary API key when no type is given', function () {
    $this->actingAs(mcpKeyUser())
        ->postJson('/playground/tokens', ['name' => 'My app'])
        ->assertCreated()
        ->assertJsonPath('abilities', TokenAbility::PLAYGROUND_ABILITIES);
});

it('rejects an unknown key type', function () {
    $this->actingAs(mcpKeyUser())
        ->postJson('/playground/tokens', ['name' => 'x', 'type' => 'admin'])
        ->assertUnprocessable();
});

// ── Verify endpoint (called by the MCP server) ────────────────────────────────

it('needs a key to verify MCP keys', function () {
    $this->postJson('/api/v1/mcp/verify-key', ['key' => 'ak_x'])->assertUnauthorized();
});

it('refuses callers without mcp:verify-keys', function () {
    $this->withHeader('X-Api-Key', mcpRawKey([TokenAbility::MCP_USE]))
        ->postJson('/api/v1/mcp/verify-key', ['key' => 'ak_x'])
        ->assertForbidden();
});

it('accepts a valid MCP key and records its use', function () {
    $userKey = mcpRawKey([TokenAbility::MCP_USE]);

    $this->withHeader('X-Api-Key', mcpRawKey([TokenAbility::MCP_VERIFY_KEYS]))
        ->postJson('/api/v1/mcp/verify-key', ['key' => $userKey])
        ->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('key_prefix', substr($userKey, 0, 12));

    expect(ApiKey::where('key_hash', hash('sha256', $userKey))->first()->last_used_at)->not->toBeNull();
});

it('rejects keys that are unknown, revoked, expired or not for MCP', function () {
    $server = mcpRawKey([TokenAbility::MCP_VERIFY_KEYS]);

    $revoked = mcpRawKey([TokenAbility::MCP_USE]);
    ApiKey::where('key_hash', hash('sha256', $revoked))->update(['is_active' => false]);

    $expired = mcpRawKey([TokenAbility::MCP_USE]);
    ApiKey::where('key_hash', hash('sha256', $expired))->update(['expires_at' => now()->subMinute()]);

    $notMcp = mcpRawKey(TokenAbility::PLAYGROUND_ABILITIES);

    foreach (['ak_' . str_repeat('0', 32), $revoked, $expired, $notMcp, 'not-an-ak-key'] as $key) {
        $this->withHeader('X-Api-Key', $server)
            ->postJson('/api/v1/mcp/verify-key', ['key' => $key])
            ->assertOk()
            ->assertExactJson(['valid' => false]);
    }
});

it('validates the key field', function () {
    $this->withHeader('X-Api-Key', mcpRawKey([TokenAbility::MCP_VERIFY_KEYS]))
        ->postJson('/api/v1/mcp/verify-key', [])
        ->assertUnprocessable();
});
