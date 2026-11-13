<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\TranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TranslationResourceCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return TranslationResource::collection($this->collection)->toArray($request);
    }
}
