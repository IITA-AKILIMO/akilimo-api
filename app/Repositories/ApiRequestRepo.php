<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\ApiRequest;
use Illuminate\Database\Eloquent\Builder;
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
        $excluded = $filters['excluded'] ?? '';

        return $this->paginateWithSort(
            perPage: $perPage,
            orderBy: in_array($orderBy, $allowed) ? $orderBy : 'created_at',
            direction: $direction === 'asc' ? 'asc' : 'desc',
            filters: [
                'country_code' => $filters['country'] ?? null,
                'use_case' => $filters['use_case'] ?? null,
                'excluded' => $excluded === '' ? null : (bool)$excluded,
            ],
            search: $filters['search'] ?? null,
            searchColumns: ['device_token', 'full_names', 'phone_number'],
            scope: fn ($q) => $this->createdBetween($q, $filters),
        );
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

    /**
     * The saved advice that belongs to a farmer, narrowed to some of it when request ids are given. The query is what
     * AdviceAnonymiser releases (a soft delete: the rows stay for statistics but no longer belong to anyone).
     *
     * @param list<string> $requestIds
     * @return Builder<ApiRequest>
     */
    public function forUser(int $userId, array $requestIds = []): Builder
    {
        return $this->query()
            ->where('user_id', $userId)
            ->when($requestIds !== [], fn ($q) => $q->whereIn('request_id', $requestIds));
    }

    /** Give the advice a phone saved without an owner to a user. Only rows with no owner move; returns how many did. */
    public function claimDevice(int $userId, string $deviceToken): int
    {
        return $this->query()->where('device_token', $deviceToken)->whereNull('user_id')->update(['user_id' => $userId]);
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
        return $this->distinctValues('use_case', ['NA']);
    }
}
