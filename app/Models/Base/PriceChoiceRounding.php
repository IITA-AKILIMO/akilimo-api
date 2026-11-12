<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;

/**
 * Class PriceChoiceRounding
 *
 * @property string $currency
 * @property int $step
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceRounding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceRounding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceRounding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceRounding whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceRounding whereStep($value)
 *
 * @mixin \Eloquent
 */
class PriceChoiceRounding extends BaseModel
{
    protected $table = 'price_choice_rounding';

    protected $primaryKey = 'currency';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'step' => 'int',
    ];

    protected $fillable = [
        'step',
    ];
}
