<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\FertilizerPrice;
use Illuminate\Database\Eloquent\Collection;

class FertilizerPriceRepo extends BaseRepo
{
    protected function model(): string
    {
        return FertilizerPrice::class;
    }

    /**
     * @return Collection<int, FertilizerPrice>
     */
    public function forCountry(string $country): Collection
    {
        return $this->query()
            ->where('country', $country)
            ->orderBy('sort_order')
            ->orderBy('fertilizer_key')
            ->get();
    }

    public function deleteByIds(array $ids, string $country): void
    {
        if (empty($ids)) {
            return;
        }

        $this->query()
            ->whereIn('id', $ids)
            ->where('country', $country)
            ->delete();
    }
}
