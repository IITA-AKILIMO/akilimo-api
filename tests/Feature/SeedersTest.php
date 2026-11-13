<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

// A new database is migrated and then seeded: these checks say that is enough to get the fertilizers,
// the crops, the units and the prices, with the ids the app already holds, and that seeding twice is safe.

beforeEach(function () {
    // The admin seeder needs a password in the environment; a throwaway one, never a real credential
    config(['app.default_admin_password' => str()->random(32)]);
});

it('seeds the fertilizers, crops, units, rules and prices on a new database', function () {
    $this->seed(DatabaseSeeder::class);

    expect(DB::table('fertilizer_types')->count())->toBe(23)
        ->and(DB::table('country_fertilizers')->count())->toBe(38)
        ->and(DB::table('crops')->count())->toBe(5)
        ->and(DB::table('cassava_units')->count())->toBe(4)
        ->and(DB::table('traffic_exclusions')->count())->toBe(4)
        ->and(DB::table('starch_factories')->count())->toBe(12)
        ->and(DB::table('price_catalogue')->count())->toBe(72)
        ->and(DB::table('vw_fertilizers')->count())->toBe(38);

    // every fertilizer's catalogue label can be priced, as a fertilizer
    expect(DB::table('price_items')->where('item', 'urea')->value('category'))->toBe('FERTILIZER')
        ->and(DB::table('price_items')->where('item', 'DAP')->value('category'))->toBe('FERTILIZER');

    // the countries have their currencies
    expect(DB::table('countries')->where('code', 'NG')->value('currency_code'))->toBe('NGN')
        ->and(DB::table('countries')->where('code', 'BI')->value('currency_code'))->toBe('BIF');
});

it('keeps the ids the app and the price lists refer to', function () {
    $this->seed(DatabaseSeeder::class);

    // country_fertilizers keeps the ids of the old fertilizers table
    expect(DB::table('country_fertilizers')->where('id', 20)->value('fertilizer_type'))->toBe('NPK_20_12_26');

    // the two inactive factories added by a migration are 11 and 12, so the seeded ones are 1 to 10
    expect(DB::table('starch_factories')->where('id', 1)->value('factory_name'))->toBe('AlliedAtlanticDistilleries')
        ->and(DB::table('starch_factories')->where('id', 11)->value('factory_name'))->toBe('AyensuStarch')
        ->and(DB::table('starch_factories')->where('id', 12)->value('factory_name'))->toBe('KinaziCassavaPlant');

    // price rows keep their ids too, because the option lists are built from them (id x 10 + step)
    expect(DB::table('price_catalogue')->where('id', 55)->value('item'))->toBe('manual_ploughing')
        ->and(DB::table('price_catalogue')->max('id'))->toBe(74);
});

it('lists a fertilizer only once it has an active price, and links the prices to their fertilizers', function () {
    $this->seed(DatabaseSeeder::class);

    $priced = DB::table('vw_fertilizer_prices')->distinct()->pluck('fertilizer_key')->all();

    // NG TSP has an active price and a link to its fertilizer; NG urea is a draft until reviewed
    expect($priced)->toContain('TSP_NG')->and($priced)->not->toContain('UREA_NG');
    expect(DB::table('price_catalogue')->where('country', 'NG')->where('item', 'TSP')->value('fertilizer_type'))->toBe('TSP');
});

it('can be run again without changing what is there', function () {
    $this->seed(DatabaseSeeder::class);

    // a reviewer activates a draft and an admin edits a crop
    DB::table('price_catalogue')->where('country', 'NG')->where('item', 'urea')->update(['status' => 'active', 'price' => 51000]);
    DB::table('crops')->where('crop_key', 'maize')->update(['name' => 'Maize (edited)']);
    $before = [DB::table('price_catalogue')->count(), DB::table('country_fertilizers')->count(), DB::table('starch_factories')->count()];

    $this->seed(DatabaseSeeder::class);

    expect([DB::table('price_catalogue')->count(), DB::table('country_fertilizers')->count(), DB::table('starch_factories')->count()])->toBe($before)
        ->and((float) DB::table('price_catalogue')->where('country', 'NG')->where('item', 'urea')->value('price'))->toBe(51000.0)
        ->and(DB::table('price_catalogue')->where('country', 'NG')->where('item', 'urea')->value('status'))->toBe('active')
        ->and(DB::table('crops')->where('crop_key', 'maize')->value('name'))->toBe('Maize (edited)');
});

it('seeds the corrected Tanzanian weeding prices', function () {
    $this->seed(DatabaseSeeder::class);

    expect((float) DB::table('price_catalogue')->where('country', 'TZ')->where('item', 'weeding1')->value('price'))->toBe(60000.0)
        ->and((float) DB::table('price_catalogue')->where('country', 'TZ')->where('item', 'weeding2')->value('price'))->toBe(45000.0);
});
