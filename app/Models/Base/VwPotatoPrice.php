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
 * Class VwPotatoPrice
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereMaxUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereMinUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwPotatoPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VwPotatoPrice extends BaseModel
{
    protected $table = 'vw_potato_prices';

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
