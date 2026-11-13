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
 * Class VwCassavaPrice
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereMaxUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereMinUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwCassavaPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VwCassavaPrice extends BaseModel
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
