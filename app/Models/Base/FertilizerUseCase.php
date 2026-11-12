<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\CountryFertilizer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FertilizerUseCase
 *
 * @property string $label
 * @property int $sort_order
 * @property string $value
 * @property Collection|CountryFertilizer[] $countryFertilizers
 * @property-read int|null $country_fertilizers_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerUseCase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerUseCase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerUseCase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerUseCase whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerUseCase whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerUseCase whereValue($value)
 *
 * @mixin \Eloquent
 */
class FertilizerUseCase extends BaseModel
{
    protected $table = 'fertilizer_use_cases';

    protected $primaryKey = 'value';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'label',
        'sort_order',
    ];

    public function countryFertilizers(): HasMany
    {
        return $this->hasMany(CountryFertilizer::class, 'use_case');
    }
}
