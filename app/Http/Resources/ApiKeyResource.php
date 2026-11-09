<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\ApiKey;
use Illuminate\Http\Request;

/**
 * @mixin ApiKey
 */
class ApiKeyResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key_prefix' => $this->key_prefix,
            'abilities' => $this->abilities ?? ['*'],
            'is_active' => $this->is_active,
            'last_used_at' => $this->formatDate($this->last_used_at),
            'expires_at' => $this->formatDate($this->expires_at),
            'created_at' => $this->formatDate($this->created_at),
        ];
    }
}
