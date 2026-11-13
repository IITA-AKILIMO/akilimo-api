<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\StarchPrice;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<StarchPrice>
 */
class StarchPriceRepo extends BaseRepo
{
    protected function model(): string
    {
        return StarchPrice::class;
    }

    /**
     * @return Collection<int, StarchPrice>
     */
    public function forFactory(int $factoryId): Collection
    {
        return $this->query()
            ->where('starch_factory_id', $factoryId)
            ->orderBy('price_class')
            ->get();
    }

    public function deleteByIds(array $ids, int $factoryId): void
    {
        if (empty($ids)) {
            return;
        }

        $this->query()
            ->whereIn('id', $ids)
            ->where('starch_factory_id', $factoryId)
            ->delete();
    }
}
