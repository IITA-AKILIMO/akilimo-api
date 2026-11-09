<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Data\MinMaxPriceDto;
use App\Models\CassavaPrice;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CassavaPriceResource extends JsonResource
{
    private ?MinMaxPriceDto $priceBand = null;

    private ?Currency $currency = null;

    public static function makeWithContext($resource, MinMaxPriceDto $priceBand, ?Currency $currency = null): self
    {
        $instance = new self($resource);
        $instance->priceBand = $priceBand;
        $instance->currency = $currency;

        return $instance;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CassavaPrice $cassavaPrice */
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
            /** Minimum local price */
            'min_local_price' => $cassavaPrice->min_local_price,
            /** Maximum local price */
            'max_local_price' => $cassavaPrice->max_local_price,
            /** Currency details */
            'currency' => $currencyResource,
            /** Average price */
            'average_price' => $avgPrice,
            /** Whether this is an exact price */
            'exact_price' => $avgPrice === -1.0,
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Minimum allowed price band */
            'min_allowed_price' => $this->priceBand->minPrice,
            /** Maximum allowed price band */
            'max_allowed_price' => $this->priceBand->maxPrice,
            /** Whether the price is active */
            'active' => $cassavaPrice->price_active,
            /** Sort order for display */
            'sort_order' => $cassavaPrice->sort_order,
        ];
    }
}
