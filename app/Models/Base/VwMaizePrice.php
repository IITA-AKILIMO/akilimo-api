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
 * Class VwMaizePrice
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
 * @property bool $price_active
 * @property string $produce_type
 * @property int $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereMaxUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereMinUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereProduceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwMaizePrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VwMaizePrice extends BaseModel
{
    protected $table = 'vw_maize_prices';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'min_local_price' => 'float',
        'max_local_price' => 'float',
        'min_usd' => 'int',
        'max_usd' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'price_active' => 'boolean',
        'sort_order' => 'int',
    ];

    public $guarded = [
        '*',
    ];
}
