<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class PotatoPrice
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property int $id
 * @property float|null $max_local_price
 * @property float|null $max_price
 * @property int $max_usd
 * @property float|null $min_local_price
 * @property float|null $min_price
 * @property int $min_usd
 * @property int $price_active
 * @property int $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereMaxUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereMinUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PotatoPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PotatoPrice extends BaseModel
{
    protected $table = 'potato_prices';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'min_local_price' => 'float',
        'max_local_price' => 'float',
        'min_usd' => 'int',
        'max_usd' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'price_active' => 'int',
        'sort_order' => 'int',
    ];

    public $guarded = [
        '*',
    ];
}
