<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\ApiKey;

/**
 * @extends BaseRepo<ApiKey>
 */
class ApiKeyRepo extends BaseRepo
{
    protected function model(): string
    {
        return ApiKey::class;
    }

    /** The key with this SHA-256 hash, its owner loaded, or null */
    public function findByHash(string $hash): ?ApiKey
    {
        return $this->query(['user'])->where('key_hash', $hash)->first();
    }

    /** Record a use without firing model events */
    public function touchLastUsed(ApiKey $key): void
    {
        $key->forceFill(['last_used_at' => now()])->saveQuietly();
    }

    /** Delete every key of a user; returns how many were removed */
    public function deleteForUser(int $userId): int
    {
        return $this->query()->where('user_id', $userId)->delete();
    }
}
