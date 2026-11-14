<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Support\Facades\DB;

// A database view is read-only and computed from other tables, so its name says so: it starts with vw_.

it('names every database view with the vw_ prefix', function () {
    $views = collect(DB::select('SELECT table_name AS name FROM information_schema.views WHERE table_schema = DATABASE()'))
        ->pluck('name')
        ->map(fn ($name) => (string) $name);

    expect($views)->not->toBeEmpty();
    expect($views->reject(fn (string $name) => str_starts_with($name, 'vw_'))->values()->all())
        ->toBe([], 'These views must be renamed to start with vw_');
});

it('keeps the eight price and fertilizer views under their new names', function () {
    $views = collect(DB::select('SELECT table_name AS name FROM information_schema.views WHERE table_schema = DATABASE()'))
        ->pluck('name')
        ->map(fn ($name) => (string) $name)
        ->sort()
        ->values()
        ->all();

    expect($views)->toBe([
        'vw_cassava_prices',
        'vw_fertilizer_prices',
        'vw_fertilizers',
        'vw_investment_amount',
        'vw_maize_prices',
        'vw_operation_costs',
        'vw_potato_prices',
        'vw_price_bands',
    ]);
});

it('has no table or view left under a name the views used to have', function () {
    foreach (['fertilizers', 'fertilizer_prices', 'cassava_prices', 'potato_prices', 'maize_prices', 'investment_amount', 'operation_costs', 'price_bands'] as $old) {
        $found = DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$old]);

        expect((int) $found->n)->toBe(0, "{$old} should not exist any more");
    }
});
