<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\DefaultPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DefaultPrice
 */
class DefaultPriceResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** ISO 3166-1 alpha-2 country code */
            'country' => $this->country,
            /** Region, null for a national price */
            'region' => $this->region,
            /** Item name */
            'item' => $this->item,
            /** Open-market reference price */
            'price' => $this->price,
            /** Programme farmer price where a subsidy exists */
            'subsidised_price' => $this->subsidised_price,
            /** Unit of measurement */
            'unit' => $this->unit,
            /** Kilograms per bag, when the unit is per_bag */
            'bag_kg' => $this->bag_kg,
            /** ISO 4217 currency code */
            'currency' => $this->currency,
            /** Lowest price clients should accept */
            'min_price' => $this->min_price,
            /** Highest price clients should accept */
            'max_price' => $this->max_price,
            /** Row lifecycle: draft, pending_second_review or active */
            'status' => $this->status?->value ?? $this->status,
            /** Where the price comes from */
            'source' => $this->source,
            /** Date of the source price */
            'source_date' => $this->source_date?->toDateString(),
            /** high, medium or low */
            'confidence' => $this->confidence,
            /** First date the price applies */
            'effective_from' => $this->effective_from?->toDateString(),
            /** Timestamp when created */
            'created_at' => $this->created_at?->toDateTimeString(),
            /** Timestamp when last updated */
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
