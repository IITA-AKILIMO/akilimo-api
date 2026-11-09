<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Enums\PriceBasis;
use App\Enums\PriceStatus;
use App\Models\Base\DefaultPrice as BaseDefaultPrice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $country
 * @property string|null $region
 * @property string $item
 * @property float $price
 * @property float|null $subsidised_price
 * @property string $unit
 * @property int|null $bag_kg
 * @property float|null $min_price
 * @property float|null $max_price
 * @property PriceStatus $status
 * @property string|null $source
 * @property Carbon|null $source_date
 * @property string|null $confidence
 * @property Carbon|null $effective_from
 * @property int|null $updated_by
 * @property int|null $submitted_by
 */
class DefaultPrice extends BaseDefaultPrice
{
    public $incrementing = true;

    protected $fillable = [
        'country',
        'region',
        'item',
        'price',
        'subsidised_price',
        'unit',
        'bag_kg',
        'currency',
        'min_price',
        'max_price',
        'status',
        'source',
        'source_date',
        'confidence',
        'effective_from',
        'updated_by',
        'submitted_by',
    ];

    protected $casts = [
        'price' => 'float',
        'subsidised_price' => 'float',
        'bag_kg' => 'int',
        'min_price' => 'float',
        'max_price' => 'float',
        'status' => PriceStatus::class,
        'source_date' => 'date',
        'effective_from' => 'date',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(DefaultPriceHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === PriceStatus::Active;
    }

    /**
     * The price that applies for a country's basis: the subsidised price when
     * the basis is `subsidised` and one exists, otherwise the open-market price.
     */
    public function referencePrice(string|PriceBasis $basis = PriceBasis::OpenMarket): float
    {
        $basis = $basis instanceof PriceBasis ? $basis : PriceBasis::tryFrom($basis);

        if ($basis === PriceBasis::Subsidised && $this->subsidised_price !== null) {
            return $this->subsidised_price;
        }

        return $this->price;
    }
}
