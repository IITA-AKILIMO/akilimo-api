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
 * Class OperationCost
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereMaxCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereMinCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereOperationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereOperationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationCost whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class OperationCost extends BaseModel
{
    protected $table = 'operation_costs';

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
