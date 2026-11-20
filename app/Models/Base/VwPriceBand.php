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
 * Class VwPriceBand
 *
 * @property string $country
 * @property int $id
 * @property int|null $is_first
 * @property int|null $is_last
 * @property string $item
 * @property float|null $max_local_price
 * @property float|null $min_local_price
 * @property float $step_order
 * @property Carbon|null $updated_at
 */
class VwPriceBand extends BaseModel
{
    protected $table = 'vw_price_bands';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'step_order' => 'float',
        'min_local_price' => 'float',
        'max_local_price' => 'float',
        'is_first' => 'int',
        'is_last' => 'int',
    ];

    public $guarded = [
        '*',
    ];
}
