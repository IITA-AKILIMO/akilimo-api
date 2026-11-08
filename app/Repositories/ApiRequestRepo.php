<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\ApiRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepo<ApiRequest>
 */
class ApiRequestRepo extends BaseRepo
{
    protected function model(): string
    {
        return ApiRequest::class;
    }

    public function paginate(
        int $perPage = 20,
        string $orderBy = 'created_at',
        string $direction = 'desc',
        array $filters = [],
    ): LengthAwarePaginator {
        $allowed = ['created_at', 'country_code', 'use_case', 'request_duration_ms'];
        $orderBy = in_array($orderBy, $allowed) ? $orderBy : 'created_at';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $query = $this->model->newQuery();

        if (!empty($filters['country'])) {
            $query->where('country_code', $filters['country']);
        }

        if (!empty($filters['use_case'])) {
            $query->where('use_case', $filters['use_case']);
        }

        if (isset($filters['excluded']) && $filters['excluded'] !== '') {
            $query->where('excluded', (bool)$filters['excluded']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('device_token', 'like', "%{$term}%")
                ->orWhere('full_names', 'like', "%{$term}%")
                ->orWhere('phone_number', 'like', "%{$term}%")
            );
        }

        return $query->orderBy($orderBy, $direction)->paginate($perPage);
    }

    public function playgroundHistory(int $userId, int $limit = 30): Collection
    {
        return $this->model->newQuery()
            ->where('device_token', 'playground-' . $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get([
                'id',
                'request_id',
                'country_code',
                'use_case',
                'fr',
                'ic',
                'pp',
                'sph',
                'spp',
                'request_duration_ms',
                'created_at',
                'plumber_response',
            ]);
    }

    public function useCases(): array
    {
        return $this->model->newQuery()
            ->select('use_case')
            ->whereNotNull('use_case')
            ->where('use_case', '!=', 'NA')
            ->distinct()
            ->orderBy('use_case')
            ->pluck('use_case')
            ->all();
    }
}
