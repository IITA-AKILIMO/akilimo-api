<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Traits;

use Illuminate\Http\Request;

trait HasPaginationParams
{
    protected const int MAX_PER_PAGE = 100;

    protected function getPerPage(Request $request, int $default = 50): int
    {
        return min(max(1, (int)$request->input('per_page', $default)), self::MAX_PER_PAGE);
    }

    protected function getSortDirection(Request $request): string
    {
        $sort = strtolower((string)$request->input('sort', 'asc'));

        return in_array($sort, ['asc', 'desc'], true) ? $sort : 'asc';
    }

    protected function getOrderBy(Request $request, array $allowed, string $default): string
    {
        $orderBy = (string)$request->input('order_by', $default);

        return in_array($orderBy, $allowed, true) ? $orderBy : $default;
    }

    protected function getSortParams(Request $request, array $allowed, string $defaultColumn): array
    {
        return [
            'column' => $this->getOrderBy($request, $allowed, $defaultColumn),
            'direction' => $this->getSortDirection($request),
        ];
    }

    protected function extractDbFilters(Request $request, array $allowed = []): array
    {
        $filters = $allowed ? $request->only($allowed) : $request->all();
        unset($filters['per_page'], $filters['order_by'], $filters['sort']);

        // Normalize empty strings to null
        return array_map(fn ($v) => $v === '' ? null : $v, $filters);
    }
}
