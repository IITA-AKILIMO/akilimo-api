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
 * Class PotatoPrice
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property int $id
 * @property float $max_local_price
 * @property float $max_price
 * @property float $max_usd
 * @property float $min_local_price
 * @property float $min_price
 * @property float $min_usd
 * @property bool|null $price_active
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

    protected $casts = [
        'min_local_price' => 'float',
        'max_local_price' => 'float',
        'min_usd' => 'float',
        'max_usd' => 'float',
        'min_price' => 'float',
        'max_price' => 'float',
        'price_active' => 'bool',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'country',
        'min_local_price',
        'max_local_price',
        'min_usd',
        'max_usd',
        'min_price',
        'max_price',
        'price_active',
        'sort_order',
    ];
}
