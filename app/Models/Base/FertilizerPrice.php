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
 * Class FertilizerPrice
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property string $desc
 * @property string|null $fertilizer_key
 * @property int $id
 * @property float|null $max_price
 * @property float|null $min_price
 * @property int $price_active
 * @property float|null $price_per_bag
 * @property float $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereFertilizerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice wherePricePerBag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FertilizerPrice extends BaseModel
{
    protected $table = 'fertilizer_prices';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'price_per_bag' => 'float',
        'price_active' => 'int',
        'sort_order' => 'float',
    ];

    public $guarded = [
        '*',
    ];
}
