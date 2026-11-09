<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Currency;
use App\Models\FertilizerPrice;
use App\Utils\CurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FertilizerPriceResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly ?FertilizerPrice $minBand,
        private readonly ?FertilizerPrice $maxBand,
        private readonly ?Currency $currency,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var FertilizerPrice $fertilizerPrice */
        $fertilizerPrice = $this->resource;
        $conv = new CurrencyConversion;
        $currencyResource = $this->currency ? CurrencyResource::make($this->currency) : null;

        $priceRange = $conv->convertPriceToLocalCurrency(
            minUsd: $fertilizerPrice->price_per_bag,
            maxUsd: $fertilizerPrice->price_per_bag,
            currencyRate: 1,
            nearestValue: 1000.0,
            currency: $this->currency
        );

        return [
            /** Unique identifier for the price record */
            'id' => $fertilizerPrice->id,
            /** Same as id, for compatibility */
            'price_id' => $fertilizerPrice->id,
            /** Fertilizer key identifier */
            'fertilizer_key' => $fertilizerPrice->fertilizer_key,
            /** Country and id composite identifier */
            'fertilizer_country' => "{$fertilizerPrice->country}{$fertilizerPrice->id}",
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $fertilizerPrice->country,
            /** Currency details */
            'currency' => $currencyResource,
            /** Sort order for display */
            'sort_order' => $fertilizerPrice->sort_order,
            /** Minimum local price */
            'min_local_price' => $fertilizerPrice->min_price,
            /** Maximum local price */
            'max_local_price' => $fertilizerPrice->max_price,
            /** Minimum allowed price range */
            'min_allowed_price' => $this->minBand?->min_price,
            /** Maximum allowed price range */
            'max_allowed_price' => $this->maxBand?->max_price,
            /** Price per bag */
            'price_per_bag' => $fertilizerPrice->price_per_bag,
            /** Converted price range */
            'price_range' => $priceRange,
            /** Whether the price is active */
            'active' => $fertilizerPrice->price_active,
            /** Description or notes */
            'description' => $fertilizerPrice->desc,
            /** Timestamp when created */
            'created_at' => $fertilizerPrice->created_at,
            /** Timestamp when last updated */
            'updated_at' => $fertilizerPrice->updated_at,
        ];
    }
}
