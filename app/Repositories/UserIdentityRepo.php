<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\UserIdentity;

/**
 * @extends BaseRepo<UserIdentity>
 */
class UserIdentityRepo extends BaseRepo
{
    protected function model(): string
    {
        return UserIdentity::class;
    }

    /** The linked sign-in for a provider account, its user loaded, or null */
    public function findByProvider(string $provider, string $subject): ?UserIdentity
    {
        return $this->query(['user'])->where('provider', $provider)->where('subject', $subject)->first();
    }

    /** Link a provider account to a user unless it is linked already */
    public function link(string $provider, string $subject, int $userId): UserIdentity
    {
        return $this->query()->firstOrCreate(['provider' => $provider, 'subject' => $subject], ['user_id' => $userId]);
    }

    public function deleteForUser(int $userId): int
    {
        return $this->query()->where('user_id', $userId)->delete();
    }
}
