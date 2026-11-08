<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Support\Facades\DB;

function insertDefaultPrice(string $country, string $item, float $price): void
{
    DB::table('default_prices')->insert([
        'country' => $country,
        'item' => $item,
        'price' => $price,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function runBuToBiMigration(): void
{
    (require database_path('migrations/2026_11_08_000001_rename_bu_default_prices_to_bi.php'))->up();
}

it('moves legacy BU default prices to BI with their prices unchanged', function () {
    insertDefaultPrice('BU', 'cassUP', 700000);
    insertDefaultPrice('BU', 'FOMIIMBURA', 85390);

    runBuToBiMigration();
    runBuToBiMigration(); // idempotent

    expect(DB::table('default_prices')->where('country', 'BU')->count())->toBe(0)
        ->and(DB::table('default_prices')->where('country', 'BI')->whereIn('item', ['cassUP', 'FOMIIMBURA'])->pluck('price', 'item')->all())
        ->toEqual(['cassUP' => 700000, 'FOMIIMBURA' => 85390]);
});

it('keeps an existing BI row and drops its BU duplicate', function () {
    insertDefaultPrice('BI', 'cassUP', 650000);
    insertDefaultPrice('BU', 'cassUP', 700000);

    runBuToBiMigration();

    expect(DB::table('default_prices')->where('item', 'cassUP')->pluck('price', 'country')->all())
        ->toEqual(['BI' => 650000]);
});

it('returns Burundi default prices for country=BI and the legacy BU alias', function (string $country) {
    insertDefaultPrice('BI', 'cassUP', 700000);
    insertDefaultPrice('BI', 'FOMIBAGARA', 77600);
    insertDefaultPrice('NG', 'cassUP', 1000);

    $response = $this->getJson("/api/v1/default-prices?country={$country}")->assertOk();

    expect(collect($response->json('data'))->pluck('country')->unique()->values()->all())->toBe(['BI'])
        ->and($response->json('data'))->toHaveCount(2);
})->with(['BI', 'BU', 'bu']);
