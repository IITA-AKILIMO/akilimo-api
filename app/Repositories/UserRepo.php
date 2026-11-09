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
