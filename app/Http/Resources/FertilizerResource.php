<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Http\Enums\EnumCountry;
use App\Models\VwFertilizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VwFertilizer
 */
class FertilizerResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** Unique identifier for the fertilizer */
            'id' => $this->id,
            /** Human-readable display name */
            'name' => $this->name,
            /** Fertilizer type (e.g. STRAIGHT, COMPOUND) */
            'type' => $this->type,
            /** Kind of fertilizer: STRAIGHT (one nutrient), COMPOUND (several) or OTHER */
            'category' => $this->category,
            /** Stable identifier key for the fertilizer */
            'fertilizer_key' => $this->fertilizer_key,
            /** Default bag weight in kg */
            'weight' => $this->weight,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $this->country,
            /** ISO 4217 currency code */
            'currency_code' => EnumCountry::fromCode($this->country)->currency(),
            /** Sort order for display */
            'sort_order' => $this->sort_order,
            /** Use case(s) the fertilizer is applicable for */
            'use_case' => $this->use_case,
            /** Cereal intensity score */
            'cis' => $this->cis,
            /** Cereal intensity mean */
            'cim' => $this->cim,
            /** Whether the fertilizer is currently available */
            'available' => $this->available,
            /** Timestamp when created */
            'created_at' => $this->formatDate($this->created_at),
            /** Timestamp when last updated */
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }
}
