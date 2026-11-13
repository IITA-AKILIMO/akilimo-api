<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\ApiRequest;

/**
 * One saved recommendation of a farmer, as listed. The detail view (`detail` set) adds the stored result.
 *
 * @mixin ApiRequest
 */
class FarmerHistoryResource extends BaseJsonResource
{
    public function __construct($resource, private readonly bool $detail = false)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $data = [
            'request_id' => $this->request_id,
            'country_code' => $this->country_code,
            'use_case' => $this->use_case,
            'fr' => $this->fr,
            'ic' => $this->ic,
            'pp' => $this->pp,
            'sph' => $this->sph,
            'spp' => $this->spp,
            'request_duration_ms' => $this->request_duration_ms,
            'created_at' => $this->formatDate($this->created_at),
        ];

        if ($this->detail) {
            $data['result'] = $this->plumber_response;
        }

        return $data;
    }
}
