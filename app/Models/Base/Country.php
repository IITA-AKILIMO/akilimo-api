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
 * Class Country
 *
 * @property bool $active
 * @property string $boundary
 * @property string $code
 * @property Carbon|null $created_at
 * @property int $id
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $max_latitude
 * @property float|null $max_longitude
 * @property float|null $min_latitude
 * @property float|null $min_longitude
 * @property string $name
 * @property int|null $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereBoundary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereMaxLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereMaxLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereMinLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereMinLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Country extends BaseModel
{
    protected $table = 'countries';

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'int',
        'latitude' => 'float',
        'longitude' => 'float',
        'min_latitude' => 'float',
        'max_latitude' => 'float',
        'min_longitude' => 'float',
        'max_longitude' => 'float',
        'boundary' => 'string',
    ];

    protected $fillable = [
        'code',
        'name',
        'active',
        'sort_order',
        'latitude',
        'longitude',
        'min_latitude',
        'max_latitude',
        'min_longitude',
        'max_longitude',
        'boundary',
    ];
}
