<?php

use App\Models\StarchFactory;
use Illuminate\Support\Facades\DB;

function runFillCurrenciesMigration(): void
{
    (require database_path('migrations/2026_11_08_000003_fill_price_currencies.php'))->up();
}

it('fills missing and obsolete currencies from the country, leaving prices and other currencies alone', function () {
    $rows = [
        ['NG', 'urea', null], ['TZ', 'urea', ''], ['GH', 'urea', 'GHC'], ['RW', 'urea', null], ['BI', 'cassUP', null],
        ['NG', 'DAP', 'USD'],
    ];
    foreach ($rows as [$country, $item, $currency]) {
        DB::table('default_prices')->insert(['country' => $country, 'item' => $item, 'price' => 100, 'currency' => $currency]);
    }
    $factory = StarchFactory::create(['factory_name' => 'F', 'factory_label' => 'F', 'country' => 'TZ', 'factory_active' => true]);
    DB::table('starch_prices')->insert(['starch_factory_id' => $factory->id, 'price_class' => 1, 'min_starch' => 0, 'price' => 5]);

    runFillCurrenciesMigration();
    runFillCurrenciesMigration(); // idempotent

    $currencies = DB::table('default_prices')->get()->mapWithKeys(fn ($r) => ["{$r->country}/{$r->item}" => $r->currency])->all();

    expect($currencies)->toEqual([
        'NG/urea' => 'NGN', 'TZ/urea' => 'TZS', 'GH/urea' => 'GHS', 'RW/urea' => 'RWF', 'BI/cassUP' => 'BIF', 'NG/DAP' => 'USD',
    ])
        ->and(DB::table('default_prices')->sum('price'))->toEqual(600)
        ->and(DB::table('starch_prices')->where('starch_factory_id', $factory->id)->value('currency'))->toBe('TZS');
});

it('seeds every default and starch price with its country currency', function () {
    $this->seed(\Database\Seeders\DefaultPricesSeeder::class);
    $this->seed(\Database\Seeders\StarchPricesSeeder::class);

    expect(DB::table('default_prices')->where(fn ($q) => $q->whereNull('currency')->orWhere('currency', ''))->count())->toBe(0)
        ->and(DB::table('starch_prices')->where(fn ($q) => $q->whereNull('currency')->orWhere('currency', ''))->count())->toBe(0)
        ->and(DB::table('default_prices')->where('country', 'GH')->distinct()->pluck('currency')->all())->toBe(['GHS']);
});
