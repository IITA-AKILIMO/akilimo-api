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
 * Class CassavaPrice
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereMaxUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereMinUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CassavaPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CassavaPrice extends BaseModel
{
    protected $table = 'vw_cassava_prices';

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
