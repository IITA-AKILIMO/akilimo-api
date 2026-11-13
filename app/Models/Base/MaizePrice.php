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
 * Class MaizePrice
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
 * @property string $produce_type
 * @property int $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereMaxUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereMinUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereProduceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizePrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MaizePrice extends BaseModel
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
        'price_active' => 'int',
        'sort_order' => 'int',
    ];

    public $guarded = [
        '*',
    ];
}
