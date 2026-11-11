<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Database\Seeders\PriceCatalogueSeeder;
use Illuminate\Support\Facades\DB;

function tzFertilizerMigration(): object
{
    return require database_path('migrations/2026_11_08_000004_update_tz_fertilizer_default_prices.php');
}

it('updates TZ fertiliser defaults that still hold the old price, and keeps edited ones', function () {
    DB::table('price_catalogue')->insert([
        ['country' => 'TZ', 'item' => 'urea', 'price' => 58000],
        ['country' => 'TZ', 'item' => 'DAP', 'price' => 99000], // edited by an admin
        ['country' => 'NG', 'item' => 'urea', 'price' => 58000], // other country
    ]);

    tzFertilizerMigration()->up();
    tzFertilizerMigration()->up(); // idempotent

    $prices = DB::table('price_catalogue')->whereIn('country', ['TZ', 'NG'])->get()->mapWithKeys(fn ($r) => ["{$r->country}/{$r->item}" => (float)$r->price])->all();
    expect($prices)->toEqual(['TZ/urea' => 77100.0, 'TZ/DAP' => 99000.0, 'NG/urea' => 58000.0]);

    tzFertilizerMigration()->down();
    expect((float)DB::table('price_catalogue')->where(['country' => 'TZ', 'item' => 'urea'])->value('price'))->toBe(58000.0);
});

it('seeds the TFRA September 2025 TZ fertiliser prices', function () {
    $this->seed(PriceCatalogueSeeder::class);

    $tz = DB::table('price_catalogue')->where('country', 'TZ')->pluck('price', 'item')->map(fn ($p) => (float)$p);
    expect($tz->only(['urea', 'CAN', 'DAP', 'MOP', 'NPK171717', 'NPK201010', 'TSP'])->all())->toEqual([
        'urea' => 77100.0, 'CAN' => 61000.0, 'DAP' => 125500.0, 'MOP' => 76000.0,
        'NPK171717' => 89200.0, 'NPK201010' => 89200.0, 'TSP' => 103100.0,
    ]);
});
