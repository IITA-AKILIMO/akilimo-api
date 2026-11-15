<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 */
class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /** Unique identifier for the country */
            'id' => $this->id,
            /** ISO 3166-1 alpha-2 country code */
            'code' => $this->code,
            /** Full name of the country */
            'name' => $this->name,
            /** Whether the country is active */
            'active' => $this->active,
            /** Sort order for display */
            'sort_order' => $this->sort_order,
            /** Centre of the country, for a map (null when unknown) */
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            /** Bounding box of the country, for fitting a map (null when unknown) */
            'min_latitude' => $this->min_latitude,
            'max_latitude' => $this->max_latitude,
            'min_longitude' => $this->min_longitude,
            'max_longitude' => $this->max_longitude,
        ];
    }
}
