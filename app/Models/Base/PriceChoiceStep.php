<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;

/**
 * Class PriceChoiceStep
 *
 * @property float $band_half_pct
 * @property float $change_pct
 * @property int $step_order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceStep query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceStep whereBandHalfPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceStep whereChangePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceChoiceStep whereStepOrder($value)
 *
 * @mixin \Eloquent
 */
class PriceChoiceStep extends BaseModel
{
    protected $table = 'price_choice_steps';

    protected $primaryKey = 'step_order';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'step_order' => 'int',
        'change_pct' => 'float',
        'band_half_pct' => 'float',
    ];

    protected $fillable = [
        'change_pct',
        'band_half_pct',
    ];
}
