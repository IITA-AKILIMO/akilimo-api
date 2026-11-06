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
 * Class ProducePrice
 *
 * @property string $country
 * @property Carbon $created_at
 * @property int $id
 * @property bool $is_active
 * @property float $is_max_price
 * @property float $is_min_price
 * @property float $max_price
 * @property float $min_price
 * @property string $produce_name
 * @property int $sort_order
 * @property Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereIsMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereIsMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereProduceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProducePrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProducePrice extends BaseModel
{
    protected $table = 'produce_prices';

    protected $casts = [
        'min_price' => 'float',
        'max_price' => 'float',
        'is_min_price' => 'float',
        'is_max_price' => 'float',
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'country',
        'produce_name',
        'min_price',
        'max_price',
        'is_min_price',
        'is_max_price',
        'is_active',
        'sort_order',
    ];
}
