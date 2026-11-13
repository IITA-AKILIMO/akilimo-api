<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\FertilizerType as BaseFertilizerType;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A fertiliser, once: its type code (UREA, NPK_20_10_10), the label the catalogue
 * and engine use (urea, NPK201010), its name and usual bag weight. The type code
 * is the primary key. Which countries sell it is CountryFertilizer. A new
 * fertiliser is a row here.
 *
 * @property string $type
 * @property string|null $label
 * @property string $name
 * @property int $weight
 * @property string $category STRAIGHT, COMPOUND or OTHER (fertilizer_categories)
 */
class FertilizerType extends BaseFertilizerType
{
    /**
     * @return HasMany<CountryFertilizer, $this>
     */
    public function countries(): HasMany
    {
        return $this->countryFertilizers();
    }
}
