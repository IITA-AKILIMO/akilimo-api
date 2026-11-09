<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\OperationCostResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OperationCostResourceCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return OperationCostResource::collection($this->collection)->toArray($request);
    }
}
