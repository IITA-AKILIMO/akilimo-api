<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\DefaultPrice;
use App\Models\User;
use App\Service\PriceLookupService;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function lookupAdmin(): User
{
    return User::create([
        'name' => 'Lookup Admin',
        'username' => 'lookup_admin_' . str()->random(8),
        'email' => 'lookup_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

function lookupPrice(array $overrides = []): array
{
    return array_merge([
        'country' => 'NG',
        'item' => 'urea',
        'price' => 30000,
        'unit' => 'per_bag',
        'bag_kg' => 50,
        'currency' => 'NGN',
        'source' => 'Test',
        'source_date' => '2026-11-10',
        'confidence' => 'high',
    ], $overrides);
}

beforeEach(function () {
    seedCurrencies([
        ['country_code' => 'NG', 'country' => 'Nigeria', 'currency_name' => 'Nigerian naira', 'currency_code' => 'NGN', 'currency_symbol' => '₦'],
        ['country_code' => 'TZ', 'country' => 'Tanzania', 'currency_name' => 'Tanzanian shilling', 'currency_code' => 'TZS', 'currency_symbol' => 'TSh'],
        // a currency is one row; countries that share it each name it in countries.currency_code
    ]);

    // a price needs a country that exists
    foreach (['NG' => 'Nigeria', 'TZ' => 'Tanzania'] as $code => $name) {
        DB::table('countries')->insert(['code' => $code, 'name' => $name, 'active' => true, 'default_price_basis' => 'open_market']);
    }
});

it('reads the lists from the database', function () {
    $lookups = app(PriceLookupService::class);

    expect(collect($lookups->units())->pluck('value')->all())->toBe(['per_bag', 'per_acre', 'per_tonne', 'per_kg', 'per_cob'])
        ->and(collect($lookups->currencies())->pluck('code')->all())->toContain('NGN', 'TZS')
        ->and(collect($lookups->currencies())->where('code', 'TZS')->count())->toBe(1)
        ->and($lookups->currencyByCountry())->toMatchArray(['NG' => 'NGN', 'TZ' => 'TZS'])
        ->and(collect($lookups->items())->firstWhere('item', 'cassUP'))->toBe(['item' => 'cassUP', 'unit' => 'per_tonne'])
        ->and(collect($lookups->items())->firstWhere('item', 'weeding1')['unit'])->toBe('per_acre');
});

it('follows a unit or item added to the database', function () {
    DB::table('price_units')->insert(['value' => 'per_litre', 'label' => 'Per litre', 'sort_order' => 9]);
    DB::table('price_items')->insert(['item' => 'herbicide', 'unit' => 'per_litre']);

    $lookups = app(PriceLookupService::class);

    expect(collect($lookups->units())->pluck('value')->all())->toContain('per_litre')
        ->and(collect($lookups->items())->firstWhere('item', 'herbicide'))->toBe(['item' => 'herbicide', 'unit' => 'per_litre']);
});

it('gives the price forms the lists', function () {
    $this->actingAs(lookupAdmin())->get('/admin/default-prices/create')
        ->assertInertia(fn (Assert $page) => $page
            ->component('DefaultPrices/Create')
            ->has('currencies')
            ->has('units', 5)
            ->has('items')
            ->where('currencyByCountry.NG', 'NGN')
        );
});

it('accepts a price whose unit, item and currency are in the lists', function () {
    $this->actingAs(lookupAdmin())->post('/admin/default-prices', lookupPrice())->assertRedirect('/admin/default-prices');

    expect(DefaultPrice::where('country', 'NG')->where('item', 'urea')->exists())->toBeTrue();
});

it('refuses a unit, item or currency that is not in the lists', function () {
    $admin = lookupAdmin();

    $this->actingAs($admin)->post('/admin/default-prices', lookupPrice(['unit' => 'per_kilo']))->assertSessionHasErrors('unit');
    $this->actingAs($admin)->post('/admin/default-prices', lookupPrice(['item' => 'cass UP']))->assertSessionHasErrors('item');
    $this->actingAs($admin)->post('/admin/default-prices', lookupPrice(['currency' => 'XXX']))->assertSessionHasErrors('currency');

    expect(DefaultPrice::where('country', 'NG')->where('item', 'urea')->exists())->toBeFalse();
});

it('saves an item as the list spells it, whatever case was posted', function () {
    $this->actingAs(lookupAdmin())->post('/admin/default-prices', lookupPrice(['item' => 'cassup', 'unit' => 'per_tonne']))
        ->assertRedirect('/admin/default-prices');

    expect(DB::table('default_prices')->where('country', 'NG')->value('item'))->toBe('cassUP');
});

it('applies the same lists to the batch forms', function () {
    $this->actingAs(lookupAdmin())->post('/admin/default-prices/batch', [
        'country' => 'NG',
        'rows' => [
            ['item' => 'urea', 'price' => 30000, 'unit' => 'per_bag', 'currency' => 'NGN'],
            ['item' => 'weeding1', 'price' => 30000, 'unit' => 'per_hour', 'currency' => 'XXX'],
        ],
    ])->assertSessionHasErrors(['rows.1.unit', 'rows.1.currency']);

    expect(DefaultPrice::where('country', 'NG')->count())->toBe(0);
});

it('takes the default unit for an item from the database', function () {
    $price = app(\App\Service\PriceCatalogueService::class)->createDraft(
        ['country' => 'NG', 'item' => 'cassUP', 'price' => 12000, 'currency' => 'NGN'],
        lookupAdmin(),
    );

    expect($price->unit)->toBe('per_tonne')->and($price->bag_kg)->toBeNull();

    DB::table('price_items')->where('item', 'cassUP')->update(['unit' => 'per_kg']);

    $other = app(\App\Service\PriceCatalogueService::class)->createDraft(
        ['country' => 'TZ', 'item' => 'cassUP', 'price' => 180000, 'currency' => 'TZS'],
        lookupAdmin(),
    );

    expect($other->unit)->toBe('per_kg');
});
