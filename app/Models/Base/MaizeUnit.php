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
 * Class MaizeUnit
 *
 * @property Carbon|null $created_at
 * @property string|null $description
 * @property int $id
 * @property bool $is_active
 * @property bool $is_default
 * @property string $label
 * @property int $sort_order
 * @property float $unit_weight
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereUnitWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaizeUnit whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MaizeUnit extends BaseModel
{
    protected $table = 'maize_units';

    protected $casts = [
        'unit_weight' => 'float',
        'sort_order' => 'int',
        'is_default' => 'bool',
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'label',
        'unit_weight',
        'description',
        'sort_order',
        'is_default',
        'is_active',
    ];
}
