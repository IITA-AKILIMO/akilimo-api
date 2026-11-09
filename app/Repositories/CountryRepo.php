<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<Country>
 */
class CountryRepo extends BaseRepo
{
    protected function model(): string
    {
        return Country::class;
    }

    /**
     * @return Collection<int, Country>
     */
    public function allActive(): Collection
    {
        return $this->query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
