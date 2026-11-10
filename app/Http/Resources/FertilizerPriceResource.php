<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Currency;
use App\Models\FertilizerPrice;
use App\Support\PriceMarker;
use App\Utils\CurrencyConversion;
use Illuminate\Support\Facades\DB;
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

        // "I don't know" and "Exact price" are shown as words, not as a price (0 TSh, -1 TSh)
        $marker = PriceMarker::describe($fertilizerPrice->price_per_bag);

        $priceRange = $marker ?? $conv->convertPriceToLocalCurrency(
            minUsd: $fertilizerPrice->price_per_bag,
            maxUsd: $fertilizerPrice->price_per_bag,
            currencyRate: 1,
            // Same step the generated options are rounded to (price_choice_rounding), so the label matches the price sent
            nearestValue: (float) (DB::table('price_choice_rounding')->where('currency', strtoupper((string) $this->currency?->currency_code))->value('step') ?? 1000),
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
            /** Converted price range; the words "I don't know" or "Exact price" for those two rows */
            'price_range' => $priceRange,
            /** The text to show for this choice (same as price_range), which the client maps back to the value it sends */
            'label' => $priceRange,
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
