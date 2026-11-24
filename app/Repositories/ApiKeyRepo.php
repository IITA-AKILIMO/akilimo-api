<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * A user's keys, newest first, with the columns the playground lists (never the hash)
     *
     * @return Collection<int, ApiKey>
     */
    public function listForUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'key_prefix', 'is_active', 'abilities', 'last_used_at', 'expires_at', 'created_at']);
    }

    /** One of a user's own keys; 404 when it does not exist or belongs to someone else */
    public function findForUserOrFail(int $id, int $userId): ApiKey
    {
        return $this->query()->where('id', $id)->where('user_id', $userId)->firstOrFail();
    }

    /** Delete every key of a user; returns how many were removed */
    public function deleteForUser(int $userId): int
    {
        return $this->query()->where('user_id', $userId)->delete();
    }
}
