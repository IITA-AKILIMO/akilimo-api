<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Fertilizer;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<Fertilizer>
 */
class FertilizerRepo extends BaseRepo
{
    protected function model(): string
    {
        return Fertilizer::class;
    }

    /**
     * @return Collection<int, Fertilizer>
     */
    public function forCountry(string $country): Collection
    {
        return $this->query()
            ->where('country', $country)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }
}
