<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\VwOperationCost;
use App\Support\PriceMarker;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin VwOperationCost
 */
class OperationCostResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        $avgCost = ($this->min_cost + $this->max_cost) / 2;
        $tag = "{$this->id}";
        if ($avgCost === -1.0) {
            $tag = 'exact';
        }

        return [
            /** Unique identifier for the operation cost */
            'id' => $this->id,
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Name of the operation */
            'operation_name' => Str::upper($this->operation_name),
            /** Type of the operation */
            'operation_type' => Str::upper($this->operation_type),
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $this->country_code,
            /** Minimum cost */
            'min_cost' => $this->min_cost,
            /** Maximum cost */
            'max_cost' => $this->max_cost,
            /** Average cost */
            'average_cost' => $avgCost,
            /** The text to show for this choice: its cost range */
            'label' => PriceMarker::labelForRange($this->min_cost, $this->max_cost),
            /** Whether the record is active */
            'is_active' => $this->is_active,
            /** Timestamp when created */
            'created_at' =>$this->formatDate($this->created_at),
            /** Timestamp when last updated */
            'updated_at' =>$this->formatDate($this->updated_at),
        ];
    }
}
