<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\VwCassavaPrice;
use App\Models\Currency;
use App\Support\PriceMarker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CassavaPriceResource extends JsonResource
{
    private ?Currency $currency = null;

    public static function makeWithContext($resource, ?Currency $currency = null): self
    {
        $instance = new self($resource);
        $instance->currency = $currency;

        return $instance;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var VwCassavaPrice $cassavaPrice */
        $cassavaPrice = $this->resource;
        $currencyResource = $this->currency ? CurrencyResource::make($this->currency) : null;
        $avgPrice = ($cassavaPrice->min_local_price + $cassavaPrice->max_local_price) / 2;
        $tag = "{$cassavaPrice->id}";
        if ($avgPrice === -1.0) {
            $tag = 'exact';
        }

        return [
            /** Unique identifier for the price record */
            'id' => $cassavaPrice->id,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $cassavaPrice->country,
            /** Currency details */
            'currency' => $currencyResource,
            /** Average price */
            'average_price' => $avgPrice,
            /** Whether this is an exact price */
            'exact_price' => $avgPrice === -1.0,
            /** The text to show for this choice: its price range with the currency, or "Exact price" for the row where the farmer types their own */
            'label' => PriceMarker::labelForRange($cassavaPrice->min_local_price, $cassavaPrice->max_local_price, $this->currency?->currency_symbol ?: $this->currency?->currency_code),
            /** Words for the exact-price row ("Exact price"); empty for an ordinary price band */
            'description' => PriceMarker::describe($avgPrice) ?? '',
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Whether the price is active */
            'active' => $cassavaPrice->price_active,
            /** Sort order for display */
            'sort_order' => $cassavaPrice->sort_order,
        ];
    }
}
