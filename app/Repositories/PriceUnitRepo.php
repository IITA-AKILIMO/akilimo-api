<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\PriceUnit;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<PriceUnit>
 */
class PriceUnitRepo extends BaseRepo
{
    protected function model(): string
    {
        return PriceUnit::class;
    }

    /**
     * Value and label of every unit, in display order.
     *
     * @return Collection<int, PriceUnit>
     */
    public function listing(): Collection
    {
        return $this->query()->orderBy('sort_order')->orderBy('value')->get(['value', 'label']);
    }
}
