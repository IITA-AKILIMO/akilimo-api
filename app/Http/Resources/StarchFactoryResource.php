<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\StarchFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StarchFactoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        /** @var StarchFactory $starchFactory */
        $starchFactory = $this->resource;

        return [
            /** Unique identifier for the starch factory */
            'id' => $starchFactory->id,
            /** Name of the starch factory */
            'factory_name' => $starchFactory->factory_name,
            /** Label/short code for the factory */
            'factory_label' => $starchFactory->factory_label,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $starchFactory->country,
            /** Sort order for display */
            'sort_order' => $starchFactory->sort_order,
            /** Whether the factory is active */
            'factory_active' => $starchFactory->factory_active,
            /** Timestamp when created */
            'created_at' => $starchFactory->created_at,
            /** Timestamp when last updated */
            'updated_at' => $starchFactory->updated_at,
        ];
    }
}
