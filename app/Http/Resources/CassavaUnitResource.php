<?php

namespace App\Http\Resources;

use App\Models\CassavaUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CassavaUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CassavaUnit $unit */
        $unit = $this->resource;

        return [
            /** Unique identifier for the unit */
            'id' => $unit->id,
			/** Unit weight */
			'unit_weight' => $unit->unit_weight,
            /** Unit label/code */
            'label' => $unit->label,
            /** Sort order for display */
            'sort_order' => $unit->sort_order,
			'description' => $unit->description,
            /** Whether the unit is active */
			'active' => $unit->is_active,
        ];
    }
}
