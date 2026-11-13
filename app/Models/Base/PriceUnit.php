<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\PriceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PriceUnit
 *
 * @property string $label
 * @property int $sort_order
 * @property string $value
 * @property Collection|PriceItem[] $priceItems
 * @property-read int|null $price_items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceUnit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceUnit whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceUnit whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceUnit whereValue($value)
 *
 * @mixin \Eloquent
 */
class PriceUnit extends BaseModel
{
    protected $table = 'price_units';

    protected $primaryKey = 'value';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'label',
        'sort_order',
    ];

    public function priceItems(): HasMany
    {
        return $this->hasMany(PriceItem::class, 'unit');
    }
}
