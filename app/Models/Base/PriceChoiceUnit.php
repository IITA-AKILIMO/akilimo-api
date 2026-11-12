<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;

/**
 * Class PriceChoiceUnit
 *
 * @property string $item
 * @property int $quantity
 * @property int|null $rounding
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceUnit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceUnit whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceUnit whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceUnit whereRounding($value)
 *
 * @mixin \Eloquent
 */
class PriceChoiceUnit extends BaseModel
{
    protected $table = 'price_choice_units';

    protected $primaryKey = 'item';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'quantity' => 'int',
        'rounding' => 'int',
    ];

    protected $fillable = [
        'quantity',
        'rounding',
    ];
}
