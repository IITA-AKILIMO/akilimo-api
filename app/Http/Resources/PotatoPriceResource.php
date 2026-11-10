<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Support\PriceMarker;
use App\Models\PotatoPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PotatoPriceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PotatoPrice $model */
        $model = $this->resource;
        $avgPrice = ($model->min_local_price + $model->max_local_price) / 2;
        $tag = "{$model->id}";
        if ($avgPrice === -1.0) {
            $tag = 'exact';
        }

        return [
            /** Unique identifier for the price record */
            'id' => $model->id,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $model->country,
            /** Minimum local price */
            'min_local_price' => $model->min_local_price,
            /** Maximum local price */
            'max_local_price' => $model->max_local_price,
            /** Average price */
            'average_price' => $avgPrice,
            /** Whether this is an exact price */
            'exact_price' => $avgPrice === -1.0,
            /** The text to show for this choice: its price range, or "Exact price" for the row where the farmer types their own */
            'label' => PriceMarker::labelForRange($model->min_local_price, $model->max_local_price),
            /** Words for the exact-price row ("Exact price"); empty for an ordinary price band */
            'description' => PriceMarker::describe($avgPrice) ?? '',
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Minimum allowed price range */
            'min_allowed_price' => $model->min_price,
            /** Maximum allowed price range */
            'max_allowed_price' => $model->max_price,
            /** Whether the price is active */
            'active' => $model->price_active,
            /** Sort order for display */
            'sort_order' => $model->sort_order,
        ];
    }
}
