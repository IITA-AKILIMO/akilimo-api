<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A fertiliser sold in a country, and how the app uses it there. The fertiliser
 * key is the type, an underscore and the country (UREA_TZ): the fertilizers view
 * computes it, it is not stored.
 *
 * @property int $id
 * @property string $fertilizer_type
 * @property string $country
 * @property string $use_case
 * @property bool $cis
 * @property bool $cim
 * @property bool $available
 * @property int|null $sort_order
 */
class CountryFertilizer extends BaseModel
{
    protected $table = 'country_fertilizers';

    protected $casts = [
        'cis' => 'bool',
        'cim' => 'bool',
        'available' => 'bool',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'fertilizer_type',
        'country',
        'use_case',
        'cis',
        'cim',
        'available',
        'sort_order',
    ];

    /**
     * @return BelongsTo<FertilizerType, $this>
     */
    public function fertilizerType(): BelongsTo
    {
        return $this->belongsTo(FertilizerType::class, 'fertilizer_type', 'type');
    }
}
