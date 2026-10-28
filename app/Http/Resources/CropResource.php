<?php

namespace App\Http\Resources;

use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Crop $crop */
        $crop = $this->resource;

        return [
            /** Unique identifier for the crop */
            'id' => $crop->id,
            /** Stable key clients send and store, for example "cassava" */
            'key' => $crop->crop_key,
            /** Display name */
            'name' => $crop->name,
            /** Short description of what AKILIMO offers for the crop */
            'description' => $crop->description,
            /** Whether the compute service can give recommendations for this crop (false: prices and information only) */
            'advice_available' => $crop->advice_available,
            /** ISO country codes where the crop is offered; null means every country */
            'countries' => $crop->countries,
            /** Position in the list */
            'sort_order' => $crop->sort_order,
        ];
    }
}
