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
 * Class PriceCategory
 *
 * @property string $label
 * @property int $sort_order
 * @property string $value
 * @property Collection|PriceItem[] $priceItems
 * @property-read int|null $price_items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCategory whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCategory whereValue($value)
 *
 * @mixin \Eloquent
 */
class PriceCategory extends BaseModel
{
    protected $table = 'price_categories';

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
        return $this->hasMany(PriceItem::class, 'category');
    }
}
