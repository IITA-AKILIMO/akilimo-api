<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Rules\HarvestDateWindow;
use App\Support\PlantingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

function harvestPasses(string $planting, string $harvest, bool $scheduled = true): bool
{
    return Validator::make(
        ['planting' => $planting, 'harvest' => $harvest, 'recs' => ['scheduled_planting_rec' => $scheduled, 'scheduled_harvest_rec' => false]],
        ['harvest' => [new HarvestDateWindow('planting', 'recs')]],
    )->passes();
}

it('accepts a harvest 235 to 452 days after planting and rejects anything outside', function () {
    expect(harvestPasses('2026-11-28', '2027-07-21'))->toBeTrue()   // 235 days
        ->and(harvestPasses('2026-11-28', '2028-02-23'))->toBeTrue() // 452 days
        ->and(harvestPasses('2026-11-28', '2027-06-28'))->toBeFalse() // 212 days: the engine refuses it
        ->and(harvestPasses('2026-11-28', '2027-07-20'))->toBeFalse() // 234 days
        ->and(harvestPasses('2026-11-28', '2028-02-24'))->toBeFalse() // 453 days
        ->and(harvestPasses('2026-11-28', '2026-10-01'))->toBeFalse(); // before planting
});

it('only checks the window when a scheduled recommendation is asked for', function () {
    expect(harvestPasses('2026-11-28', '2027-06-28', scheduled: false))->toBeTrue();
});

it('counts whole days between the dates', function () {
    expect(PlantingSchedule::days(Carbon::parse('2026-11-28 22:00'), Carbon::parse('2027-07-21 01:00')))->toBe(235);
});
