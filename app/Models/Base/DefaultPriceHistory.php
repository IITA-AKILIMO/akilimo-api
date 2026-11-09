<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\DefaultPrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DefaultPriceHistory
 *
 * @property string $action
 * @property int|null $changed_by
 * @property string $country
 * @property Carbon|null $created_at
 * @property int|null $default_price_id
 * @property int $id
 * @property string $item
 * @property string|null $new_values
 * @property string|null $note
 * @property string|null $old_values
 * @property string|null $region
 * @property Carbon|null $updated_at
 * @property DefaultPrice|null $defaultPrice
 * @property User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereDefaultPriceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPriceHistory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DefaultPriceHistory extends BaseModel
{
    protected $table = 'default_price_history';

    protected $casts = [
        'default_price_id' => 'int',
        'changed_by' => 'int',
    ];

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

    public function defaultPrice(): BelongsTo
    {
        return $this->belongsTo(DefaultPrice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
