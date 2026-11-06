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
 * Class AvailableFertilizer
 *
 * @property bool|null $available
 * @property string $country
 * @property Carbon|null $created_at
 * @property bool|null $custom
 * @property int $id
 * @property int $k_content
 * @property int $n_content
 * @property string $name
 * @property int $p_content
 * @property float $price
 * @property int|null $sort_order
 * @property string $type
 * @property Carbon|null $updated_at
 * @property string|null $use_case
 * @property int $weight
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereKContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereNContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer wherePContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereUseCase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AvailableFertilizer whereWeight($value)
 *
 * @mixin \Eloquent
 */
class AvailableFertilizer extends BaseModel
{
    protected $table = 'available_fertilizer';

    protected $casts = [
        'n_content' => 'int',
        'p_content' => 'int',
        'k_content' => 'int',
        'weight' => 'int',
        'price' => 'float',
        'available' => 'bool',
        'custom' => 'bool',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'name',
        'type',
        'n_content',
        'p_content',
        'k_content',
        'weight',
        'price',
        'country',
        'use_case',
        'available',
        'custom',
        'sort_order',
    ];
}
