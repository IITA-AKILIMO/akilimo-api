<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Enums\PriceStatus;
use App\Models\PriceCatalogue;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class PriceCatalogueRepo extends BaseRepo
{
    protected function model(): string
    {
        return PriceCatalogue::class;
    }

    /**
     * @return Collection<int, PriceCatalogue>
     */
    public function forCountry(string $country): Collection
    {
        return $this->query()
            ->where('country', $country)
            ->orderBy('item')
            ->get();
    }

    /**
     * The rows GET /v1/prices serves: active, national and already effective.
     *
     * @return Collection<int, PriceCatalogue>
     */
    public function activeForCountry(string $country, ?CarbonInterface $updatedSince = null): Collection
    {
        return $this->query()
            ->where('country', $country)
            ->where('status', PriceStatus::Active->value)
            ->whereNull('region')
            ->where(fn ($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()->toDateString()))
            ->when($updatedSince, fn ($q) => $q->where('updated_at', '>', $updatedSince))
            ->orderBy('item')
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
