<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Currency;
use App\Models\VwFertilizerPrice;
use App\Support\PriceMarker;
use App\Utils\CurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * @mixin VwFertilizerPrice
 */
class FertilizerPriceResource extends BaseJsonResource
{
    public function __construct(
        $resource,
        private readonly ?Currency $currency,
    )
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $conv = new CurrencyConversion;
        $currencyResource = $this->currency ? CurrencyResource::make($this->currency) : null;

        // "I don't know" and "Exact price" are shown as words, not as a price (0 TSh, -1 TSh)
        $marker = PriceMarker::describe($this->price_per_bag);

        $priceRange = $marker ?? $conv->convertPriceToLocalCurrency(
            minUsd: $this->price_per_bag,
            maxUsd: $this->price_per_bag,
            currency: $this->currency,
            nearestValue: (float)(DB::table('price_choice_rounding')->where('currency', strtoupper((string)$this->currency?->currency_code))->value('step') ?? 1000)
        );

        return [
            /** Unique identifier for the price record */
            'id' => $this->id,
            /** Same as id, for compatibility */
            'price_id' => $this->id,
            /** Fertilizer key identifier */
            'fertilizer_key' => $this->fertilizer_key,
            /** Country and id composite identifier */
            'fertilizer_country' => "{$this->country}{$this->id}",
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $this->country,
            /** Currency details */
            'currency' => $currencyResource,
            /** Sort order for display */
            'sort_order' => $this->sort_order,
            /** Price per bag */
            'price_per_bag' => $this->price_per_bag,
            /** Converted price range; the words "I don't know" or "Exact price" for those two rows */
            'price_range' => $priceRange,
            /** The text to show for this choice (same as price_range), which the client maps back to the value it sends */
            'label' => $priceRange,
            /** Whether the price is active */
            'active' => $this->price_active,
            /** Description or notes */
            'description' => $this->desc,
            /** Timestamp when created */
            'created_at' => $this->formatDate($this->created_at),
            /** Timestamp when last updated */
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }
}
