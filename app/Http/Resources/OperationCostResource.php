<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\OperationCost;
use App\Support\PriceMarker;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class OperationCostResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var OperationCost $model */
        $model = $this->resource;

        $avgCost = ($model->min_cost + $model->max_cost) / 2;
        $tag = "{$model->id}";
        if ($avgCost === -1.0) {
            $tag = 'exact';
        }

        return [
            /** Unique identifier for the operation cost */
            'id' => $model->id,
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Name of the operation */
            'operation_name' => Str::upper($model->operation_name),
            /** Type of the operation */
            'operation_type' => Str::upper($model->operation_type),
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $model->country_code,
            /** Minimum cost */
            'min_cost' => $model->min_cost,
            /** Maximum cost */
            'max_cost' => $model->max_cost,
            /** Average cost */
            'average_cost' => $avgCost,
            /** The text to show for this choice: its cost range */
            'label' => PriceMarker::labelForRange($model->min_cost, $model->max_cost),
            /** Whether the record is active */
            'is_active' => $model->is_active,
            /** Timestamp when created */
            'created_at' => $model->created_at,
            /** Timestamp when last updated */
            'updated_at' => $model->updated_at,
        ];
    }
}
