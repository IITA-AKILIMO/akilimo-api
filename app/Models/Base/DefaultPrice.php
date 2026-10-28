<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class DefaultPrice
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property string|null $currency
 * @property int $id
 * @property string $item
 * @property float $price
 * @property string $unit
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DefaultPrice extends BaseModel
{
    protected $table = 'default_prices';

    protected $casts = [
        'price' => 'float',
    ];

    protected $fillable = [
        'country',
        'item',
        'price',
        'unit',
        'currency',
    ];
}
