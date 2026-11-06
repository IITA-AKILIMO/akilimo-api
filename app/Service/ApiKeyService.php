<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Auth\TokenAbility;
use App\Models\ApiKey;
use App\Models\User;

class ApiKeyService
{
    /**
     * Create a key for a user. The raw key is returned once; only its SHA-256 and the first
     * characters are stored, as AuthenticateWithToken expects.
     *
     * @param array<int, string> $abilities
     * @return array{key: ApiKey, raw: string}
     */
    public function issue(User $user, string $name, array $abilities): array
    {
        $raw = 'ak_' . bin2hex(random_bytes(16));

        $key = ApiKey::create([
            'user_id' => $user->id,
            'name' => $name,
            'key_prefix' => substr($raw, 0, 12),
            'key_hash' => hash('sha256', $raw),
            'abilities' => $abilities,
            'is_active' => true,
        ]);

        return ['key' => $key, 'raw' => $raw];
    }

    /**
     * The usable key behind a raw MCP key (active, not expired, with mcp:use), or null.
     * Records the use, like a request authenticated with the key would.
     */
    public function verifyMcpKey(string $raw): ?ApiKey
    {
        if (!str_starts_with($raw, 'ak_')) {
            return null;
        }

        $key = ApiKey::where('key_hash', hash('sha256', $raw))->with('user')->first();

        if ($key === null || !$key->isUsable() || !$key->can(TokenAbility::MCP_USE)) {
            return null;
        }

        $key->forceFill(['last_used_at' => now()])->saveQuietly();

        return $key;
    }
}
