<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A fertiliser, once: its type code (UREA, NPK_20_10_10), the label the catalogue
 * and engine use (urea, NPK201010), its name and usual bag weight. Which
 * countries sell it is CountryFertilizer. A new fertiliser is a row here.
 *
 * @property int $id
 * @property string $type
 * @property string|null $label
 * @property string $name
 * @property int $weight
 */
class FertilizerType extends BaseModel
{
    protected $table = 'fertilizer_types';

    protected $casts = [
        'weight' => 'int',
    ];

    protected $fillable = [
        'type',
        'label',
        'name',
        'weight',
    ];

    /**
     * @return HasMany<CountryFertilizer, $this>
     */
    public function countries(): HasMany
    {
        return $this->hasMany(CountryFertilizer::class);
    }
}
