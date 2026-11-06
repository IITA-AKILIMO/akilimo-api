<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Crop
 *
 * @property bool $active
 * @property bool $advice_available
 * @property list<string>|null $countries
 * @property Carbon|null $created_at
 * @property string $crop_key
 * @property string|null $description
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Crop newModelQuery()
 * @method static Builder<static>|Crop newQuery()
 * @method static Builder<static>|Crop query()
 *
 * @mixin \Eloquent
 */
class Crop extends BaseModel
{
    protected $table = 'crops';

    protected $fillable = [
        'crop_key',
        'name',
        'description',
        'active',
        'advice_available',
        'countries',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'advice_available' => 'boolean',
        'countries' => 'array',
        'sort_order' => 'integer',
    ];

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
