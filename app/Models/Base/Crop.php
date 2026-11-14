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
 * Class Crop
 *
 * @property bool $active
 * @property bool $advice_available
 * @property array|null $countries
 * @property Carbon|null $created_at
 * @property string $crop_key
 * @property string|null $description
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereAdviceAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereCountries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereCropKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Crop whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Crop extends BaseModel
{
    protected $table = 'crops';

    protected $casts = [
        'active' => 'boolean',
        'advice_available' => 'bool',
        'countries' => 'array',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'crop_key',
        'name',
        'description',
        'active',
        'advice_available',
        'countries',
        'sort_order',
    ];
}
