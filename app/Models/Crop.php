<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\Crop as BaseCrop;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handwritten behaviour lives here, not in Base\Crop, which the model generator overwrites.
 *
 * @property list<string>|null $countries
 */
class Crop extends BaseCrop
{
    /** Crops that are switched on */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Crops offered in a country: those with no country list (every country) and those that name it.
     * Done in PHP-friendly SQL (JSON contains) so it also works on SQLite.
     */
    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        $code = strtoupper($countryCode);

        return $query->where(function (Builder $q) use ($code) {
            $q->whereNull('countries')->orWhereJsonContains('countries', $code);
        });
    }
}
