<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources\Collections;

use App\Http\Enums\EnumCountry;
use App\Http\Resources\CassavaPriceResource;
use App\Models\Currency;
use App\Repositories\CassavaPriceRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CassavaPriceResourceCollection extends ResourceCollection
{
    public function __construct($resource, private readonly CassavaPriceRepo $priceRepo)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * Price bands and currencies are loaded once for all rows on this page,
     * eliminating the two N+1 queries the old per-row instantiation caused.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Collect unique countries
        $countries = $this->collection->pluck('country')->unique()->values();

        // Precompute country → currency mapping
        $countryCurrencies = $countries->mapWithKeys(
            fn ($country) => [$country => EnumCountry::fromCode($country)->currency()]
        );

        // Price bands for each country
        $priceBands = $this->priceRepo->findPriceBandsForCountries($countries->all());

        // Currency models keyed by code
        $currencies = Currency::query()
            ->whereIn('currency_code', $countryCurrencies->unique()->all())
            ->get()
            ->keyBy('currency_code');

        // Map each item into a resource with context
        $resources = $this->collection->map(function ($item) use ($priceBands, $currencies, $countryCurrencies) {
            $currencyCode = $countryCurrencies[$item->country] ?? null;

            return CassavaPriceResource::makeWithContext(
                $item,
                $priceBands[$item->country] ?? null,
                $currencyCode ? $currencies[$currencyCode] ?? null : null
            );
        });

        return $resources->toArray();
    }
}
