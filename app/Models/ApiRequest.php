<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\ApiRequest as BaseApiRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequest extends BaseApiRequest
{
    /** The farmer account this saved recommendation belongs to; null for anonymous requests. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
