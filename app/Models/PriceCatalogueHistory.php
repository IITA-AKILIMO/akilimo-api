<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\PriceCatalogueHistory as BasePriceCatalogueHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hand-written behaviour lives here, not in Base\PriceCatalogueHistory, which the model generator overwrites.
 *
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 */
class PriceCatalogueHistory extends BasePriceCatalogueHistory
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
