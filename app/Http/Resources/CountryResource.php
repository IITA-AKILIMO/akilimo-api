<?php

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Country $country */
        $country = $this->resource;

        return [
            /** Unique identifier for the country */
            'id' => $country->id,
            /** ISO 3166-1 alpha-2 country code */
            'code' => $country->code,
            /** Full name of the country */
            'name' => $country->name,
            /** Whether the country is active */
            'active' => $country->active,
            /** Sort order for display */
            'sort_order' => $country->sort_order,
            /** Centre of the country, for a map (null when unknown) */
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            /** Bounding box of the country, for fitting a map (null when unknown) */
            'min_latitude' => $country->min_latitude,
            'max_latitude' => $country->max_latitude,
            'min_longitude' => $country->min_longitude,
            'max_longitude' => $country->max_longitude,
        ];
    }
}
