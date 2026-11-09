<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Translation;
use Illuminate\Http\Request;

/**
 * @mixin Translation
 */
class TranslationResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'en' => $this->en,
            'sw' => $this->sw,
            'rw' => $this->rw,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }
}
