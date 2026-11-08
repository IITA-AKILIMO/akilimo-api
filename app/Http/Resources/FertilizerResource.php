<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Http\Enums\EnumCountry;
use App\Models\Fertilizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FertilizerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Fertilizer $fertilizer */
        $fertilizer = $this->resource;

        return [
            /** Unique identifier for the fertilizer */
            'id' => $fertilizer->id,
            /** Human-readable display name */
            'name' => $fertilizer->name,
            /** Fertilizer type (e.g. STRAIGHT, COMPOUND) */
            'type' => $fertilizer->type,
            /** Stable identifier key for the fertilizer */
            'fertilizer_key' => $fertilizer->fertilizer_key,
            /** Default bag weight in kg */
            'weight' => $fertilizer->weight,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $fertilizer->country,
            /** ISO 4217 currency code */
            'currency_code' => EnumCountry::fromCode($fertilizer->country)->currency(),
            /** Sort order for display */
            'sort_order' => $fertilizer->sort_order,
            /** Use case(s) the fertilizer is applicable for */
            'use_case' => $fertilizer->use_case,
            /** Cereal intensity score */
            'cis' => $fertilizer->cis,
            /** Cereal intensity mean */
            'cim' => $fertilizer->cim,
            /** Whether the fertilizer is currently available */
            'available' => $fertilizer->available,
            /** Timestamp when created */
            'created_at' => $fertilizer->created_at,
            /** Timestamp when last updated */
            'updated_at' => $fertilizer->updated_at,
        ];
    }
}
