<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->withoutVite());

function filterAdmin(EnumUserRole $role = EnumUserRole::Admin): User
{
    return User::create([
        'name' => ucfirst($role->value) . ' Filter',
        'username' => $role->value . '_' . Str::random(6),
        'email' => $role->value . '_' . Str::random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => $role,
    ]);
}

it('searches and filters countries by status', function () {
    DB::table('countries')->insert([
        ['code' => 'ZZ', 'name' => 'Zedland', 'active' => true, 'sort_order' => 90],
        ['code' => 'ZY', 'name' => 'Zyland', 'active' => false, 'sort_order' => 91],
    ]);
    $admin = filterAdmin();

    $this->actingAs($admin)->get('/admin/countries?search=zed')
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'zed')
            ->has('items.data', 1)
            ->where('items.data.0.code', 'ZZ'));

    $this->actingAs($admin)->get('/admin/countries?search=land&active=0')
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
            ->where('items.data.0.code', 'ZY'));
});

it('ignores an active value other than 1 or 0', function () {
    $this->actingAs(filterAdmin())->get('/admin/countries?active=yes')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('filters.active', ''));
});

it('treats % and _ in a search as plain characters', function () {
    DB::table('countries')->insert(['code' => 'ZX', 'name' => 'Plain', 'active' => true, 'sort_order' => 92]);

    $this->actingAs(filterAdmin())->get('/admin/countries?search=%25')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 0));
});

it('finds translations by their text and lists the ones still missing a language', function () {
    DB::table('translations')->insert([
        ['key' => 'filter.one', 'en' => 'Fertilizer advice', 'sw' => 'Ushauri wa mbolea', 'rw' => ''],
        ['key' => 'filter.two', 'en' => 'Planting dates', 'sw' => null, 'rw' => 'Amatariki'],
    ]);
    $admin = filterAdmin();

    $this->actingAs($admin)->get('/admin/translations?search=mbolea')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.key', 'filter.one'));

    $this->actingAs($admin)->get('/admin/translations?search=filter.&missing=sw')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.key', 'filter.two'));

    $this->actingAs($admin)->get('/admin/translations?search=filter.&missing=rw')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.key', 'filter.one'));
});

it('lists the crops offered in a country, including those offered everywhere', function () {
    seedCassavaCrop();
    DB::table('crops')->insert([
        ['crop_key' => 'yam', 'name' => 'Yam', 'active' => true, 'advice_available' => false, 'countries' => json_encode(['NG']), 'sort_order' => 5],
        ['crop_key' => 'rice', 'name' => 'Rice', 'active' => true, 'advice_available' => false, 'countries' => json_encode(['TZ']), 'sort_order' => 6],
    ]);

    // Cassava (seeded, every country) and yam are offered in Nigeria; rice is not
    $this->actingAs(filterAdmin())->get('/admin/crops?country=NG')
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.data', fn ($rows) => collect($rows)->pluck('crop_key')->sort()->values()->all() === ['cassava', 'yam']));
});

it('filters users by role and searches their names', function () {
    $admin = filterAdmin();
    filterAdmin(EnumUserRole::Partner);

    $this->actingAs($admin)->get('/admin/users?role=partner')
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.role', 'partner')
            ->where('users.data', fn ($rows) => collect($rows)->every(fn ($u) => $u['role'] === 'partner') && count($rows) >= 1));

    $this->actingAs($admin)->get('/admin/users?role=superuser')
        ->assertInertia(fn (Assert $page) => $page->where('filters.role', ''));
});
