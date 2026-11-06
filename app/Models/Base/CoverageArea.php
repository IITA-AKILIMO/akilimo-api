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
 * Class CoverageArea
 *
 * @property string $checksum
 * @property string $country_code
 * @property Carbon|null $created_at
 * @property Carbon $fetched_at
 * @property string $geojson
 * @property int $id
 * @property float $max_lat
 * @property float $max_lon
 * @property float $min_lat
 * @property float $min_lon
 * @property int|null $n_cells
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereFetchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereGeojson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereMaxLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereMaxLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereMinLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereMinLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereNCells($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverageArea whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CoverageArea extends BaseModel
{
    protected $table = 'coverage_areas';

    protected $casts = [
        'n_cells' => 'int',
        'min_lon' => 'float',
        'min_lat' => 'float',
        'max_lon' => 'float',
        'max_lat' => 'float',
        'fetched_at' => 'datetime',
    ];

    protected $fillable = [
        'country_code',
        'geojson',
        'n_cells',
        'min_lon',
        'min_lat',
        'max_lon',
        'max_lat',
        'checksum',
        'fetched_at',
    ];
}
