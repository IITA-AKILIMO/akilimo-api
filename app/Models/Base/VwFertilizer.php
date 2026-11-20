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
 * Class VwFertilizer
 *
 * @property bool $available
 * @property string $category
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereCim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereCis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereFertilizerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereFertilizerLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereUseCase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VwFertilizer whereWeight($value)
 *
 * @mixin \Eloquent
 */
class VwFertilizer extends BaseModel
{
    protected $table = 'vw_fertilizers';

    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'weight' => 'int',
        'sort_order' => 'int',
        'cis' => 'bool',
        'cim' => 'bool',
        'available' => 'bool',
    ];

    public $guarded = [
        '*',
    ];
}
