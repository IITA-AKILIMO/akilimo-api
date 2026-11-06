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
 * Class MaizePrice
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
    protected $table = 'maize_prices';

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
        'produce_type',
    ];
}
