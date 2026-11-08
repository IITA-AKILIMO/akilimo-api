<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

abstract class AdminController extends Controller
{
    /**
     * Extract named filter values from the request, stripping blank strings.
     * Accepts a map of ['requestParam' => 'dbColumn'] or ['param'] for same-name params.
     */
    protected function filtersFrom(Request $request, array $keys): array
    {
        $filters = [];
        foreach ($keys as $param => $column) {
            if (is_int($param)) {
                $param = $column;
            }
            $value = $request->get($param);
            if ($value !== null && $value !== '') {
                $filters[$column] = $value;
            }
        }

        return $filters;
    }

    /**
     * The filter values as the page sent them, for its filter bar ('' when unset).
     * An `active` filter only keeps '1' or '0', so a stray value cannot reach the query.
     *
     * @param string[] $params
     * @return array<string, string>
     */
    protected function filterValues(Request $request, array $params): array
    {
        $values = [];
        foreach ($params as $param) {
            $value = trim((string)$request->get($param, ''));
            $values[$param] = $param === 'active' && !in_array($value, ['0', '1'], true) ? '' : $value;
        }

        return $values;
    }

    /**
     * The values a free-text column actually holds, for a filter's choices
     * (a fixed list would miss values entered later, e.g. a new use case).
     *
     * @return string[]
     */
    protected function distinctValues(string $table, string $column): array
    {
        return DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->map(fn ($v) => (string)$v)
            ->all();
    }

    /**
     * Convert a LengthAwarePaginator to the Paginated<T> shape expected by the frontend.
     */
    protected function paginateShape(LengthAwarePaginator $paginator, callable $map): array
    {
        return [
            'data' => collect($paginator->items())->map($map)->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }
}
