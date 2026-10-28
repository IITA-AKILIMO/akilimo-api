<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\CoverageArea;
use Illuminate\Database\Eloquent\Collection;

class CoverageAreaRepo extends BaseRepo
{
    protected function model(): string
    {
        return CoverageArea::class;
    }

    public function findByCountry(string $country): ?CoverageArea
    {
        return $this->query()->where('country_code', strtoupper($country))->first();
    }

    /**
     * Everything except the (large) geometry, for listings.
     *
     * @return Collection<int, CoverageArea>
     */
    public function summaries(): Collection
    {
        return $this->query()
            ->select(['id', 'country_code', 'n_cells', 'min_lon', 'min_lat', 'max_lon', 'max_lat', 'checksum', 'fetched_at'])
            ->orderBy('country_code')
            ->get();
    }

    /**
     * @return Collection<int, CoverageArea>
     */
    public function allWithGeometry(): Collection
    {
        return $this->query()->orderBy('country_code')->get();
    }

    public function store(string $country, array $values): CoverageArea
    {
        return $this->query()->updateOrCreate(['country_code' => strtoupper($country)], $values);
    }
}
