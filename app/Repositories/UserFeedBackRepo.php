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
        $orderBy = in_array($orderBy, $allowed) ? $orderBy : 'created_at';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $query = $this->model->newQuery();

        if (!empty($filters['use_case'])) {
            $query->where('use_case', $filters['use_case']);
        }

        if (!empty($filters['user_type'])) {
            $query->where('user_type', $filters['user_type']);
        }

        if (!empty($filters['language'])) {
            $query->where('language', $filters['language']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where('device_token', 'like', "%{$term}%");
        }

        return $query->orderBy($orderBy, $direction)->paginate($perPage);
    }

    public function useCases(): array
    {
        return $this->model->newQuery()
            ->select('use_case')
            ->whereNotNull('use_case')
            ->distinct()
            ->orderBy('use_case')
            ->pluck('use_case')
            ->all();
    }

    public function languages(): array
    {
        return $this->model->newQuery()
            ->select('language')
            ->whereNotNull('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language')
            ->all();
    }
}
