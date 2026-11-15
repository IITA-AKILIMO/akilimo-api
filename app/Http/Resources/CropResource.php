<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Crop
 */
class CropResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** Unique identifier for the crop */
            'id' => $this->id,
            /** Stable key clients send and store, for example "cassava" */
            'key' => $this->crop_key,
            /** Display name */
            'name' => $this->name,
            /** Short description of what AKILIMO offers for the crop */
            'description' => $this->description,
            /** Whether the compute service can give recommendations for this crop (false: prices and information only) */
            'advice_available' => $this->advice_available,
            /** ISO country codes where the crop is offered; null means every country */
            'countries' => $this->countries,
            /** Position in the list */
            'sort_order' => $this->sort_order,
        ];
    }
}
