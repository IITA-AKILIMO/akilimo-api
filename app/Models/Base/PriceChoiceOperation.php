<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;

/**
 * Class PriceChoiceOperation
 *
 * @property string $item
 * @property string $operation_name
 * @property string $operation_type
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceOperation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceOperation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceOperation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceOperation whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceOperation whereOperationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceOperation whereOperationType($value)
 *
 * @mixin \Eloquent
 */
class PriceChoiceOperation extends BaseModel
{
    protected $table = 'price_choice_operations';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'item',
    ];
}
