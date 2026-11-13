<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\CountryFertilizer;
use App\Models\FertilizerCategory;
use App\Models\PriceCatalogue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FertilizerType
 *
 * @property string $category
 * @property Carbon|null $created_at
 * @property string|null $label
 * @property string $name
 * @property string $type
 * @property Carbon|null $updated_at
 * @property int $weight
 * @property Collection|CountryFertilizer[] $countryFertilizers
 * @property FertilizerCategory $fertilizerCategory
 * @property Collection|PriceCatalogue[] $priceCatalogues
 * @property-read int|null $country_fertilizers_count
 * @property-read int|null $price_catalogues_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerType whereWeight($value)
 *
 * @mixin \Eloquent
 */
class FertilizerType extends BaseModel
{
    protected $table = 'fertilizer_types';

    protected $primaryKey = 'type';

    public $incrementing = false;

    protected $casts = [
        'weight' => 'int',
    ];

    protected $fillable = [
        'label',
        'name',
        'weight',
        'category',
    ];

    public function countryFertilizers(): HasMany
    {
        return $this->hasMany(CountryFertilizer::class, 'fertilizer_type');
    }

    public function fertilizerCategory(): BelongsTo
    {
        return $this->belongsTo(FertilizerCategory::class, 'category');
    }

    public function priceCatalogues(): HasMany
    {
        return $this->hasMany(PriceCatalogue::class, 'fertilizer_type');
    }
}
