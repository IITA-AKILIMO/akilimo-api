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
 * Class VwInvestmentAmount
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereAreaUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereInvestmentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount wherePriceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwInvestmentAmount whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VwInvestmentAmount extends BaseModel
{
    protected $table = 'vw_investment_amount';

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
