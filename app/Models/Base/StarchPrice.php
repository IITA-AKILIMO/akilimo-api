<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\StarchFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StarchPrice
 *
 * @property Carbon|null $created_at
 * @property string|null $currency
 * @property int $id
 * @property float $min_starch
 * @property float $price
 * @property int $price_class
 * @property string|null $range_starch
 * @property int $starch_factory_id
 * @property Carbon|null $updated_at
 * @property StarchFactory $starchFactory
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereMinStarch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice wherePriceClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereRangeStarch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereStarchFactoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StarchPrice extends BaseModel
{
    protected $table = 'starch_prices';

    protected $casts = [
        'starch_factory_id' => 'int',
        'price_class' => 'int',
        'min_starch' => 'float',
        'price' => 'float',
    ];

    protected $fillable = [
        'starch_factory_id',
        'price_class',
        'min_starch',
        'range_starch',
        'price',
        'currency',
    ];

    public function starchFactory(): BelongsTo
    {
        return $this->belongsTo(StarchFactory::class);
    }
}
