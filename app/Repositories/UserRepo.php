<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepo<User>
 */
class UserRepo extends BaseRepo
{
    protected function model(): string
    {
        return User::class;
    }

    /** The user whose username or email is this login, or null */
    public function findByLogin(string $login): ?User
    {
        return $this->query()->where('username', $login)->orWhere('email', $login)->first();
    }

    public function usernameExists(string $username): bool
    {
        return $this->query()->where('username', $username)->exists();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /** Set columns on a user, including ones that are not mass assignable, and save it */
    public function forceUpdate(User $user, array $attributes): User
    {
        $user->forceFill($attributes)->save();

        return $user;
    }

    public function paginateWithSearch(
        string $search,
        int $perPage = 20,
        string $orderBy = 'created_at',
        string $direction = 'desc',
    ): LengthAwarePaginator {
        return $this->query()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy($orderBy, $direction)
            ->paginate($perPage);
    }
}
