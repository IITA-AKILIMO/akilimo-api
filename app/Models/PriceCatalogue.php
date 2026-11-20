<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Enums\PriceBasis;
use App\Enums\PriceStatus;
use App\Models\Base\PriceCatalogue as BasePriceCatalogue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceCatalogue extends BasePriceCatalogue
{

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
        return $this->hasMany(PriceCatalogueHistory::class);
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
