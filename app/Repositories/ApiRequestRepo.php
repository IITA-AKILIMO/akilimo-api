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

    /**
     * A farmer's saved advice, newest first. Only the columns the list shows are read: a saved row also holds the whole
     * request and answer (large JSON), which a farmer with a long history must not drag through every page.
     */
    public function paginateForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->select(['id', 'request_id', 'country_code', 'use_case', 'fr', 'ic', 'pp', 'sph', 'spp', 'request_duration_ms', 'created_at'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * The ids of a farmer's saved advice, newest first, at most $max of them (one more is read to tell whether the list was cut).
     * A client uses it to drop its own copies of items that were deleted elsewhere.
     *
     * @return array{ids: list<string>, truncated: bool}
     */
    public function requestIdsForUser(int $userId, int $max): array
    {
        $ids = $this->model->newQuery()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($max + 1)
            ->pluck('request_id')
            ->all();

        return ['ids' => array_slice($ids, 0, $max), 'truncated' => count($ids) > $max];
    }

    /** Removes all of a farmer's saved advice. Returns how many rows went. */
    public function deleteAllForUser(int $userId): int
    {
        return $this->model->newQuery()->where('user_id', $userId)->delete();
    }

    /** One saved recommendation, only when it belongs to this farmer. */
    public function findForUser(int $userId, string $requestId): ?ApiRequest
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('request_id', $requestId)
            ->first();
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
