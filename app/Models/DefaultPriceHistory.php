<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\DefaultPriceHistory as BaseDefaultPriceHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hand-written behaviour lives here, not in Base\DefaultPriceHistory, which the model generator overwrites.
 *
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 */
class DefaultPriceHistory extends BaseDefaultPriceHistory
{
    /** The price row before and after the change, stored as JSON */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
