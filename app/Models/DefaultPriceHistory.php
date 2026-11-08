<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\DefaultPriceHistory as BaseDefaultPriceHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultPriceHistory extends BaseDefaultPriceHistory
{
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
