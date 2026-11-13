<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\ApiRequest;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ApiRequest */
class ApiRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'device_token' => $this->device_token,
            'droid_request' => $this->droid_request,
            'request_duration_ms' => $this->request_duration_ms,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
