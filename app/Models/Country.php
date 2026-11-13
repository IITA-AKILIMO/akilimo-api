<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait;
use App\Enums\PriceBasis;
use App\Models\Base\Country as BaseCountry;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends BaseCountry
{
    use SpatialTrait;

    protected array $spatialFields = ['boundary'];

    protected $fillable = [
        'code',
        'name',
        'currency_code',
        'active',
        'default_price_basis',
        'sort_order',
        'latitude',
        'longitude',
        'min_latitude',
        'max_latitude',
        'min_longitude',
        'max_longitude',
        'boundary',
    ];

    protected $casts = [
        'active' => 'boolean',
        'default_price_basis' => PriceBasis::class,
        'sort_order' => 'int',
        'latitude' => 'float',
        'longitude' => 'float',
        'min_latitude' => 'float',
        'max_latitude' => 'float',
        'min_longitude' => 'float',
        'max_longitude' => 'float',
    ];

    /** Admins assigned to review this country's prices (decision D4). */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'country_reviewers', 'country', 'user_id', 'code', 'id');
    }
}
