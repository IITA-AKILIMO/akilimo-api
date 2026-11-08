<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\StarchPrice;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StarchPrice
 **/
class StarchPriceResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {

        $factory = $this->starchFactory;

        return [
            /** Composite key of factory name and price class */
            'key' => ($factory?->factory_name) . $this->price_class,
            /** Starch factory name */
            'starch_factory' => $factory?->factory_name,
            /** Starch factory label */
            'starch_factory_label' => $factory?->factory_label,
            /** Price class/grade */
            'class' => $this->price_class,
            /** ISO 3166-1 alpha-2 country code */
            'country' => $factory?->country,
            /** Minimum starch content */
            'min_starch' => $this->min_starch,
            /** Starch content range */
            'range_starch' => $this->range_starch,
            /** Price value */
            'price' => $this->price,
            /** ISO 4217 currency code */
            'currency' => $this->currency,
            /** Timestamp when created */
            'created_at' => $this->created_at?->toDateTimeString(),
            /** Timestamp when last updated */
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
