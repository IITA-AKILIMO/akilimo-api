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

    /** Is there a price for this item in this country, other than the row with $exceptId? */
    public function existsFor(string $country, string $item, int|string|null $exceptId = null): bool
    {
        return $this->query()
            ->where('country', $country)
            ->where('item', $item)
            ->when($exceptId !== null, fn ($q) => $q->whereKeyNot($exceptId))
            ->exists();
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
}
