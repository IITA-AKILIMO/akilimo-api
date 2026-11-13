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
 * Class VwOperationCost
 *
 * @property string $country_code
 * @property Carbon|null $created_at
 * @property int $id
 * @property int $is_active
 * @property float|null $max_cost
 * @property float|null $min_cost
 * @property string $operation_name
 * @property string $operation_type
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereMaxCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereMinCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereOperationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereOperationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwOperationCost whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VwOperationCost extends BaseModel
{
    protected $table = 'vw_operation_costs';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'min_cost' => 'float',
        'max_cost' => 'float',
        'is_active' => 'int',
    ];

    public $guarded = [
        '*',
    ];
}
