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
 * Class PriceBand
 *
 * @property string $country
 * @property int $id
 * @property int|null $is_first
 * @property int|null $is_last
 * @property string $item
 * @property float|null $max_local_price
 * @property float|null $min_local_price
 * @property float $step_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereIsFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereIsLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereMaxLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereMinLocalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereStepOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceBand whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PriceBand extends BaseModel
{
    protected $table = 'price_bands';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'step_order' => 'float',
        'min_local_price' => 'float',
        'max_local_price' => 'float',
        'is_first' => 'int',
        'is_last' => 'int',
    ];

    public $guarded = [
        '*',
    ];
}
