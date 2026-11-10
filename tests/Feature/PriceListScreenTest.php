<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function listAdmin(): User
{
    return User::create([
        'name' => 'List Admin',
        'username' => 'list_admin_' . str()->random(8),
        'email' => 'list_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

function listCatalogue(): void
{
    foreach (['NG' => 'NGN', 'TZ' => 'TZS'] as $code => $currency) {
        DB::table('countries')->insert(['code' => $code, 'name' => $code, 'active' => true, 'default_price_basis' => 'open_market']);
        DB::table('currencies')->insert(['country_code' => $code, 'country' => $code, 'currency_name' => $currency, 'currency_code' => $currency, 'currency_symbol' => $currency === 'NGN' ? '₦' : 'TSh']);
    }

    DB::table('default_prices')->insert([
        ['country' => 'NG', 'item' => 'cassUP', 'price' => 12000, 'unit' => 'per_tonne', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ['country' => 'TZ', 'item' => 'cassUP', 'price' => 180000, 'unit' => 'per_tonne', 'currency' => 'TZS', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

it('shows the lists the app reads, with the label a client displays for each choice', function () {
    listCatalogue();

    $this->actingAs(listAdmin())->get('/admin/price-lists?list=cassava&country=ng')
        ->assertInertia(fn (Assert $page) => $page
            ->component('PriceLists/Index')
            ->where('filters.list', 'cassava')
            ->where('filters.country', 'NG')
            ->where('title', 'Cassava prices')
            ->has('lists', 6)
            ->where('items.meta.total', 5)                       // four bands and the exact-price row
            ->where('items.data.0.label', 'Exact price')         // words, not -1
            ->where('items.data.1.label', '7,200 – 9,600 ₦')     // what the app shows, with the currency
            ->where('items.data.1.average', fn ($v) => (float) $v === 8400.0));
});

it('labels the zero and negative rows as words in the fertilizer and investment lists', function () {
    listCatalogue();
    DB::table('fertilizer_types')->insert(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50]);
    DB::table('country_fertilizers')->insert(['fertilizer_type' => 'UREA', 'country' => 'NG', 'use_case' => 'FR', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('default_prices')->insert([
        ['country' => 'NG', 'item' => 'urea', 'fertilizer_type' => 'UREA', 'price' => 10000, 'unit' => 'per_bag', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ['country' => 'NG', 'item' => 'investment_amount', 'fertilizer_type' => null, 'price' => 10000, 'unit' => 'per_acre', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
    ]);
    $admin = listAdmin();

    $this->actingAs($admin)->get('/admin/price-lists?list=fertilizer&country=NG')
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.data', fn ($rows) => collect($rows)->pluck('label')->all() === ["I don't know", '7,000 ₦', '9,000 ₦', '11,000 ₦', '13,000 ₦', 'Exact price']));

    $this->actingAs($admin)->get('/admin/price-lists?list=investment&country=NG')
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.data.0.label', 'Exact amount')
            ->where('items.data.1.label', '10,000 ₦'));
});

it('filters the rows to the choices or to the rows shown in words', function () {
    listCatalogue();
    $admin = listAdmin();

    $this->actingAs($admin)->get('/admin/price-lists?list=cassava&kind=markers')
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasMarkers', true)
            ->where('items.meta.total', 2)                       // one exact-price row per country
            ->where('items.data.0.label', 'Exact price'));

    $this->actingAs($admin)->get('/admin/price-lists?list=cassava&kind=choices')
        ->assertInertia(fn (Assert $page) => $page->where('items.meta.total', 8));

    // a list with no such rows ignores the filter
    $this->actingAs($admin)->get('/admin/price-lists?list=operations&kind=markers')
        ->assertInertia(fn (Assert $page) => $page->where('hasMarkers', false)->where('filters.kind', ''));
});

it('searches the lists that have a name to search by, and pages them', function () {
    listCatalogue();
    DB::table('fertilizer_types')->insert([
        ['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50],
        ['type' => 'DAP', 'label' => 'DAP', 'name' => 'DAP', 'weight' => 50],
    ]);
    foreach (['UREA', 'DAP'] as $type) {
        DB::table('country_fertilizers')->insert(['fertilizer_type' => $type, 'country' => 'NG', 'use_case' => 'FR', 'created_at' => now(), 'updated_at' => now()]);
    }
    $admin = listAdmin();

    // each fertilizer has "I don't know", "Exact price" (no catalogue price, so no choices)
    $this->actingAs($admin)->get('/admin/price-lists?list=fertilizer&search=dap')
        ->assertInertia(fn (Assert $page) => $page
            ->where('searchPlaceholder', fn ($p) => str_contains((string) $p, 'fertilizer key'))
            ->where('items.meta.total', 2)
            ->where('items.data.0.fertilizer_key', 'DAP_NG'));

    $this->actingAs($admin)->get('/admin/price-lists?list=fertilizer&per_page=10')
        ->assertInertia(fn (Assert $page) => $page->where('items.meta.total', 4)->where('items.meta.per_page', 10));

    // a list with no text to search has no search box
    $this->actingAs($admin)->get('/admin/price-lists?list=cassava')
        ->assertInertia(fn (Assert $page) => $page->where('searchPlaceholder', null));
});

it('falls back to the fertilizer list for a list name it does not know', function () {
    $this->actingAs(listAdmin())->get('/admin/price-lists?list=users')
        ->assertInertia(fn (Assert $page) => $page->where('filters.list', 'fertilizer'));
});

it('shows an empty list when no catalogue price feeds it', function () {
    $this->actingAs(listAdmin())->get('/admin/price-lists?list=operations')
        ->assertInertia(fn (Assert $page) => $page->where('items.meta.total', 0)->has('items.data', 0));
});

it('has a screen for every list the app reads', function () {
    foreach (['fertilizer', 'cassava', 'maize', 'potato', 'investment', 'operations'] as $list) {
        $this->actingAs(listAdmin())->get("/admin/price-lists?list={$list}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('filters.list', $list)->has('columns')->has('items.meta'));
    }
});
