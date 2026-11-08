<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\DefaultPriceHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DefaultPrice
 *
 * @property int|null $bag_kg
 * @property string|null $confidence
 * @property string $country
 * @property Carbon|null $created_at
 * @property string|null $currency
 * @property Carbon|null $effective_from
 * @property int $id
 * @property string $item
 * @property float|null $max_price
 * @property float|null $min_price
 * @property float $price
 * @property string|null $region
 * @property string|null $source
 * @property Carbon|null $source_date
 * @property string $status
 * @property int|null $submitted_by
 * @property float|null $subsidised_price
 * @property string $unit
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property Collection|DefaultPriceHistory[] $defaultPriceHistories
 * @property User|null $user
 * @property-read int|null $default_price_histories_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereBagKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereConfidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereEffectiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereSourceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereSubmittedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereSubsidisedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereUpdatedBy($value)
 *
 * @mixin \Eloquent
 */
class DefaultPrice extends BaseModel
{
    protected $table = 'default_prices';

    protected $casts = [
        'price' => 'float',
        'subsidised_price' => 'float',
        'bag_kg' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'source_date' => 'datetime',
        'effective_from' => 'datetime',
        'updated_by' => 'int',
        'submitted_by' => 'int',
    ];

    protected $fillable = [
        'country',
        'region',
        'item',
        'price',
        'subsidised_price',
        'unit',
        'bag_kg',
        'min_price',
        'max_price',
        'status',
        'source',
        'source_date',
        'confidence',
        'effective_from',
        'updated_by',
        'submitted_by',
        'currency',
    ];

    public function defaultPriceHistories(): HasMany
    {
        return $this->hasMany(DefaultPriceHistory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
