<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\PersonalAccessToken;

/**
 * @extends BaseRepo<PersonalAccessToken>
 */
class PersonalAccessTokenRepo extends BaseRepo
{
    protected function model(): string
    {
        return PersonalAccessToken::class;
    }

    /**
     * Store a hashed token for its owner.
     *
     * @param class-string $tokenableType
     * @param array<int, string> $abilities
     */
    public function issue(string $tokenableType, int $tokenableId, string $name, string $hash, array $abilities, \DateTimeInterface $expiresAt): PersonalAccessToken
    {
        return $this->create([
            'tokenable_type' => $tokenableType,
            'tokenable_id' => $tokenableId,
            'name' => $name,
            'token' => $hash,
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);
    }

    /** @param class-string $tokenableType */
    public function deleteForOwner(string $tokenableType, int $tokenableId): int
    {
        return $this->query()->where('tokenable_type', $tokenableType)->where('tokenable_id', $tokenableId)->delete();
    }
}
