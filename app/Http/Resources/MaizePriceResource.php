<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Data\MinMaxPriceDto;
use App\Models\MaizePrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaizePriceResource extends JsonResource
{
    public function __construct($resource, private readonly MinMaxPriceDto $priceBand)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var MaizePrice $model */
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
            /** Type of produce */
            'produce_type' => $model->produce_type,
            /** Minimum local price */
            'min_local_price' => $model->min_local_price,
            /** Maximum local price */
            'max_local_price' => $model->max_local_price,
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
            'active' => $model->price_active,
            /** Sort order for display */
            'sort_order' => $model->sort_order,
        ];
    }
}
