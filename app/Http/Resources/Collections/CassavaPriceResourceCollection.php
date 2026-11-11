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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CassavaPriceResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * Currencies are loaded once for all rows on this page, not once per row.
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

        // Currency models keyed by code
        $currencies = Currency::query()
            ->whereIn('currency_code', $countryCurrencies->unique()->all())
            ->get()
            ->keyBy('currency_code');

        // Map each item into a resource with context
        $resources = $this->collection->map(function ($item) use ($currencies, $countryCurrencies) {
            $currencyCode = $countryCurrencies[$item->country] ?? null;

            return CassavaPriceResource::makeWithContext(
                $item,
                $currencyCode ? $currencies[$currencyCode] ?? null : null
            );
        });

        return $resources->toArray();
    }
}
