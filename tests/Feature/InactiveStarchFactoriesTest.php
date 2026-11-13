<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\StarchFactory;
use Database\Seeders\StarchPricesSeeder;
use Illuminate\Support\Facades\DB;

function starchFactoryWithPrice(string $name, string $country, bool $active, float $price): StarchFactory
{
    $factory = StarchFactory::create([
        'factory_name' => $name,
        'factory_label' => $name,
        'country' => $country,
        'factory_active' => $active,
    ]);

    DB::table('starch_prices')->insert([
        'starch_factory_id' => $factory->id,
        'price_class' => 1,
        'min_starch' => 0,
        'range_starch' => '>0',
        'price' => $price,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $factory;
}

function runInactiveStarchMigration(): void
{
    (require database_path('migrations/2026_11_08_000002_add_inactive_gh_rw_starch_factories.php'))->up();
}

it('leaves inactive factories out of the starch price feed', function () {
    starchFactoryWithPrice('ActiveGH', 'GH', true, 1000);
    starchFactoryWithPrice('InactiveGH', 'GH', false, 900);

    $factories = collect($this->getJson('/api/v1/starch-prices?country=GH')->assertOk()->json('data'))->pluck('starch_factory');
    expect($factories->all())->toBe(['ActiveGH']);

    $all = collect($this->getJson('/api/v1/starch-prices')->assertOk()->json('data'))->pluck('starch_factory');
    expect($all->contains('InactiveGH'))->toBeFalse();
});

it('leaves inactive factories out of the per-country factory list', function () {
    starchFactoryWithPrice('ActiveRW', 'RW', true, 300000);
    starchFactoryWithPrice('InactiveRW', 'RW', false, 330000);

    $names = collect($this->getJson('/api/v1/starch-factories/country/RW')->assertOk()->json('data'))->pluck('factory_name');
    expect($names->all())->toBe(['ActiveRW']);
});

it('adds the GH and RW factories inactive with their estimated prices, once', function () {
    runInactiveStarchMigration();
    runInactiveStarchMigration(); // idempotent

    $rows = DB::table('starch_factories')
        ->join('starch_prices', 'starch_prices.starch_factory_id', '=', 'starch_factories.id')
        ->whereIn('factory_name', ['AyensuStarch', 'KinaziCassavaPlant'])
        ->get(['factory_name', 'country', 'factory_active', 'price', 'currency'])
        ->keyBy('factory_name');

    expect($rows)->toHaveCount(2)
        ->and($rows['AyensuStarch']->country)->toBe('GH')
        ->and((float)$rows['AyensuStarch']->price)->toBe(900.0)
        ->and($rows['AyensuStarch']->currency)->toBe('GHS')
        ->and((float)$rows['KinaziCassavaPlant']->price)->toBe(330000.0)
        ->and($rows['KinaziCassavaPlant']->currency)->toBe('RWF')
        ->and($rows->every(fn ($r) => !$r->factory_active))->toBeTrue();

    expect($this->getJson('/api/v1/starch-prices?country=GH')->json('data'))->toBe([]);
});

it('seeds the CSV factories with their active flag and currency', function () {
    $this->seed(StarchPricesSeeder::class);

    $factories = StarchFactory::whereIn('factory_name', ['AlliedAtlanticDistilleries', 'AyensuStarch', 'KinaziCassavaPlant'])
        ->pluck('factory_active', 'factory_name');

    expect($factories->all())->toEqual(['AlliedAtlanticDistilleries' => true, 'AyensuStarch' => false, 'KinaziCassavaPlant' => false])
        ->and(DB::table('starch_prices')->where('currency', 'RWF')->value('price'))->toEqual(330000);
});

it('keeps a price an admin already set', function () {
    $factory = starchFactoryWithPrice('AyensuStarch', 'GH', true, 1200);

    runInactiveStarchMigration();

    expect((float)DB::table('starch_prices')->where('starch_factory_id', $factory->id)->value('price'))->toBe(1200.0)
        ->and((bool)$factory->fresh()->factory_active)->toBeTrue();
});
