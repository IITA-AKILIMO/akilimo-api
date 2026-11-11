<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Core\Models\BaseModel;

/**
 * A unit maize grain is sold in (see migration 2026_11_11_000001). The label is the
 * name of the app's unit of sale (HUNDRED_KG); the weight is in kilograms.
 *
 * @property int $id
 * @property string $label
 * @property float $unit_weight
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_default
 * @property bool $is_active
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
