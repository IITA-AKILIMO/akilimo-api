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
    foreach (['NG', 'TZ'] as $code) {
        DB::table('countries')->insert(['code' => $code, 'name' => $code, 'active' => true, 'default_price_basis' => 'open_market']);
    }

    DB::table('default_prices')->insert([
        ['country' => 'NG', 'item' => 'cassUP', 'price' => 12000, 'unit' => 'per_tonne', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ['country' => 'TZ', 'item' => 'cassUP', 'price' => 180000, 'unit' => 'per_tonne', 'currency' => 'TZS', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

it('shows the lists the app reads, as computed from the catalogue', function () {
    listCatalogue();

    $this->actingAs(listAdmin())->get('/admin/price-lists?list=cassava')
        ->assertInertia(fn (Assert $page) => $page
            ->component('PriceLists/Index')
            ->where('list', 'cassava')
            ->where('title', 'Cassava prices')
            ->has('lists', 6)
            ->where('total', 10)   // four bands and the exact-price row, for two countries
            ->where('columns.3.key', 'average'));
});

it('filters by country and shows the average the app sends', function () {
    listCatalogue();

    $this->actingAs(listAdmin())->get('/admin/price-lists?list=cassava&country=ng')
        ->assertInertia(fn (Assert $page) => $page
            ->where('country', 'NG')
            ->where('total', 5)
            ->where('rows.0.country', 'NG')
            ->where('rows.1.average', fn ($v) => (float) $v === 8400.0));
});

it('falls back to the fertilizer list for a list name it does not know', function () {
    $this->actingAs(listAdmin())->get('/admin/price-lists?list=users')
        ->assertInertia(fn (Assert $page) => $page->where('list', 'fertilizer'));
});

it('shows an empty list when no catalogue price feeds it', function () {
    $this->actingAs(listAdmin())->get('/admin/price-lists?list=operations')
        ->assertInertia(fn (Assert $page) => $page->where('total', 0)->has('rows', 0));
});

it('has a screen for every list the app reads', function () {
    foreach (['fertilizer', 'cassava', 'maize', 'potato', 'investment', 'operations'] as $list) {
        $this->actingAs(listAdmin())->get("/admin/price-lists?list={$list}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('list', $list)->has('columns'));
    }
});
