<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\PriceCategory;
use App\Models\PriceUnit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PriceItem
 *
 * @property string $category
 * @property string $item
 * @property string $unit
 * @property PriceCategory $priceCategory
 * @property PriceUnit $priceUnit
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceItem whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceItem whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceItem whereUnit($value)
 *
 * @mixin \Eloquent
 */
class PriceItem extends BaseModel
{
    protected $table = 'price_items';

    protected $primaryKey = 'item';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'unit',
        'category',
    ];

    public function priceCategory(): BelongsTo
    {
        return $this->belongsTo(PriceCategory::class, 'category');
    }

    public function priceUnit(): BelongsTo
    {
        return $this->belongsTo(PriceUnit::class, 'unit');
    }
}
