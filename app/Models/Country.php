<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait;
use App\Models\Base\Country as BaseCountry;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends BaseCountry
{
    use SpatialTrait;

    protected array $spatialFields = ['boundary'];

    /** Admins assigned to review this country's prices (decision D4). */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'country_reviewers', 'country', 'user_id', 'code', 'id');
    }
}
