<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;

/**
 * Class PriceChoiceMultiple
 *
 * @property float $multiple
 * @property int $step_order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceMultiple newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceMultiple newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceMultiple query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceMultiple whereMultiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceMultiple whereStepOrder($value)
 *
 * @mixin \Eloquent
 */
class PriceChoiceMultiple extends BaseModel
{
    protected $table = 'price_choice_multiples';

    protected $primaryKey = 'step_order';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'step_order' => 'int',
        'multiple' => 'float',
    ];

    protected $fillable = [
        'multiple',
    ];
}
