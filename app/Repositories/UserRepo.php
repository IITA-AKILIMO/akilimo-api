<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Enums\EnumUserRole;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

    /** Whether the id belongs to a farmer account (role user), not an admin or partner. */
    public function isFarmer(int|string $id): bool
    {
        return $this->query()->whereKey($id)->where('role', EnumUserRole::User)->exists();
    }

    /**
     * Farmer accounts matching a search on name, e-mail or user name, or whose id is in $alsoIds (found by another
     * route, such as the phone number of their advice requests). Wildcards in the search are taken literally.
     *
     * @param array<int, int> $alsoIds
     * @return Collection<int, User>
     */
    public function searchFarmers(string $term, array $alsoIds, int $limit): Collection
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';

        return $this->query()->where('role', EnumUserRole::User)
            ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('username', 'like', $like)
                ->orWhereIn('id', $alsoIds))
            ->orderBy('name')->limit($limit)->get();
    }

    /**
     * Farmers who agreed to the terms (consent_at) under a different consent version than the current one, limited to
     * the given account ids (the ones with a phone registered for push).
     *
     * @param array<int, int> $userIds
     * @return Collection<int, User>
     */
    public function farmersOnOlderConsent(string $version, array $userIds): Collection
    {
        return $this->query()->where('role', EnumUserRole::User)
            ->whereNotNull('consent_at')
            ->where(fn ($q) => $q->whereNull('consent_version')->orWhere('consent_version', '!=', $version))
            ->whereIn('id', $userIds)
            ->get();
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
