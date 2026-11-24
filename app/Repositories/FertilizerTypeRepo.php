<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\FertilizerType;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<FertilizerType>
 */
class FertilizerTypeRepo extends BaseRepo
{
    protected function model(): string
    {
        return FertilizerType::class;
    }

    /**
     * Type, name and label of every fertiliser, by name.
     *
     * @return Collection<int, FertilizerType>
     */
    public function forPriceForms(): Collection
    {
        return $this->query()->orderBy('name')->get(['type', 'name', 'label']);
    }
}
