<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail for the catalogue: every create, update and status change.
 *
 * @property int $id
 * @property int|null $default_price_id
 * @property string $country
 * @property string $item
 * @property string|null $region
 * @property string $action
 * @property array|null $old_values
 * @property array|null $new_values
 * @property int|null $changed_by
 * @property string|null $note
 */
class DefaultPriceHistory extends BaseModel
{
    protected $table = 'default_price_history';

    protected $fillable = [
        'default_price_id',
        'country',
        'item',
        'region',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'note',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function defaultPrice(): BelongsTo
    {
        return $this->belongsTo(DefaultPrice::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
