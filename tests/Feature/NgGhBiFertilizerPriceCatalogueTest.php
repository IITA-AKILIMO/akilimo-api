<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Database\Seeders\PriceCatalogueSeeder;
use Illuminate\Support\Facades\DB;

function ngGhBiFertilizerMigration(): object
{
    return require database_path('migrations/2026_11_27_000002_update_ng_gh_bi_fertilizer_default_prices.php');
}

it('updates stale NG, GH and BI defaults that still hold the old price, and keeps edited ones', function () {
    DB::table('price_catalogue')->insert([
        ['country' => 'NG', 'item' => 'NPK171717', 'price' => 7800],
        ['country' => 'NG', 'item' => 'NPK201216', 'price' => 9000], // edited by an admin
        ['country' => 'GH', 'item' => 'NPK201010', 'price' => 146],
        ['country' => 'BI', 'item' => 'FOMIBAGARA', 'price' => 77600],
        ['country' => 'TZ', 'item' => 'NPK171717', 'price' => 7800], // other country
    ]);

    ngGhBiFertilizerMigration()->up();
    ngGhBiFertilizerMigration()->up(); // idempotent

    $row = fn (string $country, string $item) => DB::table('price_catalogue')->where(['country' => $country, 'item' => $item])->first();

    expect((float)$row('NG', 'NPK171717')->price)->toBe(50300.0)
        ->and((float)$row('NG', 'NPK201216')->price)->toBe(9000.0)
        ->and((float)$row('GH', 'NPK201010')->price)->toBe(420.0)
        ->and((float)$row('BI', 'FOMIBAGARA')->price)->toBe(77600.0)
        ->and((float)$row('BI', 'FOMIBAGARA')->subsidised_price)->toBe(28000.0)
        ->and((float)$row('TZ', 'NPK171717')->price)->toBe(7800.0);

    ngGhBiFertilizerMigration()->down();
    expect((float)$row('NG', 'NPK171717')->price)->toBe(7800.0)
        ->and((float)$row('GH', 'NPK201010')->price)->toBe(146.0)
        ->and($row('BI', 'FOMIBAGARA')->subsidised_price)->toBeNull();
});

it('seeds the updated NG, GH and BI fertiliser prices', function () {
    $this->seed(PriceCatalogueSeeder::class);

    $price = fn (string $country, string $item) => DB::table('price_catalogue')->where(['country' => $country, 'item' => $item])->first();

    expect((float)$price('NG', 'NPK171717')->price)->toBe(50300.0)
        ->and((float)$price('GH', 'NPK123017')->price)->toBe(420.0)
        ->and((float)$price('BI', 'FOMIIMBURA')->subsidised_price)->toBe(31000.0);
});
