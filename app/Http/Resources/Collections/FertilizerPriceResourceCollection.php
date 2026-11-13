<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources\Collections;

use App\Http\Enums\EnumCountry;
use App\Http\Resources\FertilizerPriceResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FertilizerPriceResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * Currencies are loaded in one query for the whole page, not one per row.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // One query for all currencies needed on this page
        $currencyCodes = $this->collection
            ->map(fn ($item) => EnumCountry::fromCode($item->country)->currency())
            ->unique()
            ->values()
            ->all();
        $currencies = Currency::whereIn('currency_code', $currencyCodes)
            ->get()
            ->keyBy('currency_code');

        return $this->collection->map(function ($item) use ($currencies, $request) {
            $currencyCode = EnumCountry::fromCode($item->country)->currency();
            $currency = $currencies->get($currencyCode);

            return (new FertilizerPriceResource($item, $currency))->toArray($request);
        })->toArray();
    }
}
