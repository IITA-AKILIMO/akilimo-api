<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class Fertilizer
 *
 * @property bool|null $available
 * @property bool $cim
 * @property bool $cis
 * @property string $country
 * @property Carbon|null $created_at
 * @property string|null $fertilizer_key
 * @property string|null $fertilizer_label
 * @property int $id
 * @property string $name
 * @property int|null $sort_order
 * @property string $type
 * @property Carbon|null $updated_at
 * @property string $use_case
 * @property int $weight
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereCim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereCis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereFertilizerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereFertilizerLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereUseCase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fertilizer whereWeight($value)
 *
 * @mixin \Eloquent
 */
class Fertilizer extends BaseModel
{
    protected $table = 'fertilizers';

    protected $casts = [
        'weight' => 'int',
        'sort_order' => 'int',
        'cis' => 'bool',
        'cim' => 'bool',
        'available' => 'bool',
    ];

    protected $fillable = [
        'fertilizer_label',
        'name',
        'type',
        'fertilizer_key',
        'weight',
        'country',
        'sort_order',
        'use_case',
        'cis',
        'cim',
        'available',
    ];
}
