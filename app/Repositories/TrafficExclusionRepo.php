<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\TrafficExclusion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class TrafficExclusionRepo extends BaseRepo
{
    protected function model(): string
    {
        return TrafficExclusion::class;
    }

    /**
     * The rules that are switched on.
     *
     * @return Collection<int, TrafficExclusion>
     */
    public function active(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Run the UPDATE that sets api_requests.excluded; returns the number of rows that changed.
     *
     * @param array<int, mixed> $bindings
     */
    public function updateRequestFlags(string $sql, array $bindings): int
    {
        return DB::update($sql, $bindings);
    }

    /**
     * Run one SELECT of per-rule match counts over api_requests and return its single row.
     *
     * @param array<int, string> $selects
     * @param array<int, mixed> $bindings
     * @return array<string, mixed>
     */
    public function requestCounts(array $selects, array $bindings): array
    {
        return (array)DB::selectOne('SELECT ' . implode(', ', $selects) . ' FROM api_requests', $bindings);
    }

    /** Whether MariaDB accepts a REGEXP pattern */
    public function patternIsValid(string $pattern): bool
    {
        try {
            DB::selectOne("SELECT '' REGEXP ? AS ok", [$pattern]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
