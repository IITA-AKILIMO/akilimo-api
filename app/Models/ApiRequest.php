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
    /** The account column lives here, not in the generated Base model, so regenerating it keeps it. */
    public function __construct(array $attributes = [])
    {
        $this->mergeFillable(['user_id']);
        $this->mergeCasts(['user_id' => 'int']);

        parent::__construct($attributes);
    }

    /** The farmer account this saved recommendation belongs to; null for anonymous requests. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
