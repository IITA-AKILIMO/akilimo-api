<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\UserFeedback;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepo<UserFeedback>
 */
class UserFeedBackRepo extends BaseRepo
{
    protected function model(): string
    {
        return UserFeedback::class;
    }

    public function paginate(
        int $perPage = 20,
        string $orderBy = 'created_at',
        string $direction = 'desc',
        array $filters = [],
    ): LengthAwarePaginator {
        $allowed = ['created_at', 'akilimo_rec_rating', 'akilimo_useful_rating', 'user_type'];

        return $this->paginateWithSort(
            perPage: $perPage,
            orderBy: in_array($orderBy, $allowed) ? $orderBy : 'created_at',
            direction: $direction === 'asc' ? 'asc' : 'desc',
            filters: [
                'use_case' => $filters['use_case'] ?? null,
                'user_type' => $filters['user_type'] ?? null,
                'language' => $filters['language'] ?? null,
            ],
            search: $filters['search'] ?? null,
            searchColumns: ['device_token'],
            scope: fn ($q) => $this->createdBetween($q, $filters),
        );
    }

    public function useCases(): array
    {
        return $this->distinctValues('use_case');
    }

    public function languages(): array
    {
        return $this->distinctValues('language');
    }
}
