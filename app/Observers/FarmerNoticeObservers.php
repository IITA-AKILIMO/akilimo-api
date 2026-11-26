<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Observers;

use App\Enums\PriceStatus;
use App\Models\AppVersion;
use App\Models\CountryFertilizer;
use App\Models\PriceCatalogue;
use App\Models\StarchFactory;
use App\Models\StarchPrice;
use App\Service\FarmerNotifier;

/**
 * Model events that notify farmers (see {@see FarmerNotifier}): an active price changes, the fertilizers of a country
 * change, starch factories or prices change, or the app's minimum or latest version moves. Each only queues a debounced
 * notice, and nothing happens from console commands, seeders or migrations.
 */
class FarmerNoticeObservers
{
    private const array PRICE_COLUMNS = ['status', 'price', 'subsidised_price', 'min_price', 'max_price'];

    public static function register(): void
    {
        $notifier = fn (): FarmerNotifier => app(FarmerNotifier::class);

        // Only a price farmers are served counts: a draft or a price waiting for a second review is not
        PriceCatalogue::saved(function (PriceCatalogue $p) use ($notifier): void {
            if ($p->status === PriceStatus::Active && ($p->wasRecentlyCreated || $p->wasChanged(self::PRICE_COLUMNS))) {
                $notifier()->request('prices', $p->country);
            }
        });

        CountryFertilizer::saved(function (CountryFertilizer $f) use ($notifier): void {
            if ($f->wasRecentlyCreated || $f->wasChanged('available')) {
                $notifier()->request('fertilizers', $f->country);
            }
        });
        CountryFertilizer::deleted(fn (CountryFertilizer $f) => $notifier()->request('fertilizers', $f->country));

        StarchFactory::saved(fn (StarchFactory $f) => $notifier()->request('starch', $f->country));
        StarchFactory::deleted(fn (StarchFactory $f) => $notifier()->request('starch', $f->country));
        StarchPrice::saved(fn (StarchPrice $p) => $notifier()->request('starch', $p->starchFactory?->country));
        StarchPrice::deleted(fn (StarchPrice $p) => $notifier()->request('starch', $p->starchFactory?->country));

        AppVersion::saved(function (AppVersion $v) use ($notifier): void {
            // Raising the minimum forces an update; raising only the latest offers one
            if ($v->wasChanged('min_supported')) {
                $notifier()->request('app_forced_update');
            } elseif ($v->wasChanged('latest')) {
                $notifier()->request('app_update');
            }
        });
    }
}
