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
use App\Repositories\FertilizerPriceRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FertilizerPriceResourceCollection extends ResourceCollection
{
    public function __construct($resource, private readonly FertilizerPriceRepo $priceRepo)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * Price bands (min sort_order=1, max sort_order=4) and currencies are loaded in
     * three queries for the whole page, replacing the three-queries-per-row pattern.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fertilizerKeys = $this->collection
            ->pluck('fertilizer_key')
            ->unique()
            ->values()
            ->all();

        // Two queries: one for min bands, one for max bands
        $priceBands = $this->priceRepo->findMinMaxBandsByKeys($fertilizerKeys);

        // One query for all currencies needed on this page
        $currencyCodes = $this->collection
            ->map(fn ($item) => EnumCountry::fromCode($item->country)->currency())
            ->unique()
            ->values()
            ->all();
        $currencies = Currency::whereIn('currency_code', $currencyCodes)
            ->get()
            ->keyBy('currency_code');

        return $this->collection->map(function ($item) use ($priceBands, $currencies, $request) {
            $bands = $priceBands[$item->fertilizer_key] ?? ['min' => null, 'max' => null];
            $currencyCode = EnumCountry::fromCode($item->country)->currency();
            $currency = $currencies->get($currencyCode);

            return (new FertilizerPriceResource($item, $bands['min'], $bands['max'], $currency))->toArray($request);
        })->toArray();
    }
}
