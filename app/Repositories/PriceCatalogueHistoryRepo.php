<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\PriceCatalogueHistory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<PriceCatalogueHistory>
 */
class PriceCatalogueHistoryRepo extends BaseRepo
{
    protected function model(): string
    {
        return PriceCatalogueHistory::class;
    }

    /**
     * The history of one price, newest first.
     *
     * @return Collection<int, PriceCatalogueHistory>
     */
    public function forPrice(int $priceId): Collection
    {
        return $this->query()->where('price_catalogue_id', $priceId)->latest('id')->get();
    }
}
