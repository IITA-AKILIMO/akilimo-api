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
 * Class VwFertilizerPrice
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property string $desc
 * @property string|null $fertilizer_key
 * @property int $id
 * @property float|null $max_price
 * @property float|null $min_price
 * @property bool $price_active
 * @property float|null $price_per_bag
 * @property float $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereFertilizerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice wherePricePerBag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizerPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VwFertilizerPrice extends BaseModel
{
    protected $table = 'vw_fertilizer_prices';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'price_per_bag' => 'float',
        'price_active' => 'boolean',
        'sort_order' => 'float',
    ];

    public $guarded = [
        '*',
    ];
}
