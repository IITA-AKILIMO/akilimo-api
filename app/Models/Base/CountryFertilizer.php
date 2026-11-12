<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\FertilizerType;
use App\Models\FertilizerUseCase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CountryFertilizer
 *
 * @property bool $available
 * @property bool $cim
 * @property bool $cis
 * @property string $country
 * @property Carbon|null $created_at
 * @property string $fertilizer_type
 * @property int $id
 * @property int|null $sort_order
 * @property Carbon|null $updated_at
 * @property string $use_case
 * @property FertilizerType $fertilizerType
 * @property FertilizerUseCase $fertilizerUseCase
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereCim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereCis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereFertilizerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryFertilizer whereUseCase($value)
 *
 * @mixin \Eloquent
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

    public function fertilizerType(): BelongsTo
    {
        return $this->belongsTo(FertilizerType::class, 'fertilizer_type');
    }

    public function fertilizerUseCase(): BelongsTo
    {
        return $this->belongsTo(FertilizerUseCase::class, 'use_case');
    }
}
