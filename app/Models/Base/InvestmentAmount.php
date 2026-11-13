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
 * Class InvestmentAmount
 *
 * @property string $area_unit
 * @property string $country
 * @property Carbon|null $created_at
 * @property int $id
 * @property float $investment_amount
 * @property int $price_active
 * @property float $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereAreaUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereInvestmentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentAmount whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class InvestmentAmount extends BaseModel
{
    protected $table = 'investment_amount';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'investment_amount' => 'float',
        'price_active' => 'int',
        'sort_order' => 'float',
    ];

    public $guarded = [
        '*',
    ];
}
