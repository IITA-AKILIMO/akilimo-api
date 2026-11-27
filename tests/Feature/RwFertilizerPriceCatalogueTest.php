<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Database\Seeders\PriceCatalogueSeeder;
use Illuminate\Support\Facades\DB;

function rwFertilizerMigration(): object
{
    return require database_path('migrations/2026_11_27_000001_update_rw_fertilizer_default_prices.php');
}

it('updates RW fertiliser defaults that still hold the old price, and keeps edited ones', function () {
    DB::table('price_catalogue')->insert([
        ['country' => 'RW', 'item' => 'urea', 'price' => 28200],
        ['country' => 'RW', 'item' => 'DAP', 'price' => 60000], // edited by an admin
        ['country' => 'TZ', 'item' => 'urea', 'price' => 28200], // other country
    ]);

    rwFertilizerMigration()->up();
    rwFertilizerMigration()->up(); // idempotent

    $row = fn (string $country, string $item) => DB::table('price_catalogue')->where(['country' => $country, 'item' => $item])->first();

    expect((float)$row('RW', 'urea')->price)->toBe(99000.0)
        ->and((float)$row('RW', 'urea')->subsidised_price)->toBe(54500.0)
        ->and((float)$row('RW', 'DAP')->price)->toBe(60000.0)
        ->and($row('RW', 'DAP')->subsidised_price)->toBeNull()
        ->and((float)$row('TZ', 'urea')->price)->toBe(28200.0);

    rwFertilizerMigration()->down();
    expect((float)$row('RW', 'urea')->price)->toBe(28200.0)
        ->and($row('RW', 'urea')->subsidised_price)->toBeNull();
});

it('seeds the 2026/27 Rwanda fertiliser prices, full and subsidised', function () {
    $this->seed(PriceCatalogueSeeder::class);

    $rw = DB::table('price_catalogue')->where('country', 'RW')->whereIn('item', ['urea', 'DAP', 'NPK171717', 'MOP'])->get()
        ->mapWithKeys(fn ($r) => [$r->item => [(float)$r->price, $r->subsidised_price === null ? null : (float)$r->subsidised_price]])->all();

    expect($rw)->toEqual([
        'urea' => [99000.0, 54500.0],
        'DAP' => [97500.0, 53000.0],
        'NPK171717' => [93050.0, 52700.0],
        'MOP' => [27900.0, null],
    ]);
});
