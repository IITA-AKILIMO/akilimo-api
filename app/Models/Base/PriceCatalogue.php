<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Enums\PriceStatus;
use App\Models\FertilizerType;
use App\Models\PriceCatalogueHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PriceCatalogue
 *
 * @property int|null $bag_kg
 * @property string|null $confidence
 * @property string $country
 * @property Carbon|null $created_at
 * @property string|null $currency
 * @property Carbon|null $effective_from
 * @property string|null $fertilizer_type
 * @property int $id
 * @property string $item
 * @property float|null $max_price
 * @property float|null $min_price
 * @property float $price
 * @property string|null $region
 * @property string|null $source
 * @property Carbon|null $source_date
 * @property PriceStatus $status
 * @property int|null $submitted_by
 * @property float|null $subsidised_price
 * @property string $unit
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property FertilizerType|null $fertilizerType
 * @property Collection|PriceCatalogueHistory[] $priceCatalogueHistories
 * @property User|null $user
 * @property-read int|null $price_catalogue_histories_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereBagKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereConfidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereEffectiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereFertilizerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereSourceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereSubmittedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereSubsidisedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogue whereUpdatedBy($value)
 *
 * @mixin \Eloquent
 */
class PriceCatalogue extends BaseModel
{
    protected $table = 'price_catalogue';

    protected $casts = [
        'price' => 'float',
        'subsidised_price' => 'float',
        'bag_kg' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'status' => PriceStatus::class,
        'source_date' => 'date',
        'effective_from' => 'date',
        'updated_by' => 'int',
        'submitted_by' => 'int',
    ];

    protected $fillable = [
        'country',
        'region',
        'item',
        'fertilizer_type',
        'price',
        'subsidised_price',
        'unit',
        'bag_kg',
        'min_price',
        'max_price',
        'status',
        'source',
        'source_date',
        'confidence',
        'effective_from',
        'updated_by',
        'submitted_by',
        'currency',
    ];

    public function fertilizerType(): BelongsTo
    {
        return $this->belongsTo(FertilizerType::class, 'fertilizer_type');
    }

    public function priceCatalogueHistories(): HasMany
    {
        return $this->hasMany(PriceCatalogueHistory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
