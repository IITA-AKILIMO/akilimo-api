<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\VwMaizePrice;
use App\Support\PriceMarker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VwMaizePrice
 */
class MaizePriceResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        $avgPrice = ($this->min_local_price + $this->max_local_price) / 2;
        $tag = "{$this->id}";
        if ($avgPrice === -1.0) {
            $tag = 'exact';
        }

        return [
            /** Unique identifier for the price record */
            'id' => $this->id,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $this->country,
            /** Type of produce */
            'produce_type' => $this->produce_type,
            /** Average price */
            'average_price' => $avgPrice,
            /** Whether this is an exact price */
            'exact_price' => $avgPrice === -1.0,
            /** The text to show for this choice: its price range, or "Exact price" for the row where the farmer types their own */
            'label' => PriceMarker::labelForRange($this->min_local_price, $this->max_local_price),
            /** Words for the exact-price row ("Exact price"); empty for an ordinary price band */
            'description' => PriceMarker::describe($avgPrice) ?? '',
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Whether the price is active */
            'active' => $this->price_active,
            /** Sort order for display */
            'sort_order' => $this->sort_order,
        ];
    }
}
