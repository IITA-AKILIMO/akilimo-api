<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\FertilizerType;
use App\Models\User;
use App\Support\ReferenceTables;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function referenceAdmin(EnumUserRole $role = EnumUserRole::Admin): User
{
    return User::create([
        'name' => 'Reference User',
        'username' => 'ref_user_' . str()->random(8),
        'email' => 'ref_user_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => $role,
    ]);
}

function refKey(array $key): string
{
    return http_build_query(['key' => $key]);
}

it('lists every settings table on the hub', function () {
    $this->actingAs(referenceAdmin())->get('/admin/reference')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reference/Hub')
            ->has('tables', count(ReferenceTables::all()))
            ->where('tables.0.slug', 'price-units')
            ->where('tables.0.rows', 5));
});

it('keeps the settings screens to admins', function () {
    // the role middleware sends a partner away rather than showing the page
    $this->actingAs(referenceAdmin(EnumUserRole::Partner))->get('/admin/reference')->assertRedirect();
    $this->actingAs(referenceAdmin(EnumUserRole::Partner))->get('/admin/price-lists')->assertRedirect();
    $this->actingAs(referenceAdmin(EnumUserRole::Partner))->post('/admin/reference/price-units', ['value' => 'per_box', 'label' => 'Box', 'sort_order' => 1])->assertRedirect();

    expect(DB::table('price_units')->where('value', 'per_box')->exists())->toBeFalse();
});

it('shows the rows of a table with the key that addresses each', function () {
    $this->actingAs(referenceAdmin())->get('/admin/reference/price-units')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reference/Index')
            ->where('resource.slug', 'price-units')
            ->has('rows', 5)
            ->where('rows.0.value', 'per_bag')
            ->where('rows.0._key.value', 'per_bag'));
});

it('answers 404 for a table that is not a settings table', function () {
    $this->actingAs(referenceAdmin())->get('/admin/reference/users')->assertNotFound();
    $this->actingAs(referenceAdmin())->get('/admin/reference/users/create')->assertNotFound();
});

it('adds a row, refusing a bad value and a duplicate key', function () {
    $admin = referenceAdmin();

    $this->actingAs($admin)->post('/admin/reference/price-units', ['value' => 'per_litre', 'label' => 'Per litre', 'sort_order' => 6])
        ->assertRedirect('/admin/reference/price-units');

    expect(DB::table('price_units')->where('value', 'per_litre')->value('label'))->toBe('Per litre');

    $this->actingAs($admin)->post('/admin/reference/price-units', ['value' => 'Per Litre!', 'label' => 'x', 'sort_order' => 1])->assertSessionHasErrors('value');
    $this->actingAs($admin)->post('/admin/reference/price-units', ['value' => 'per_litre', 'label' => 'again', 'sort_order' => 1])->assertSessionHasErrors('value');
    $this->actingAs($admin)->post('/admin/reference/price-units', ['value' => 'per_box', 'label' => '', 'sort_order' => 1])->assertSessionHasErrors('label');
});

it('makes a select name a row that exists', function () {
    $admin = referenceAdmin();

    $this->actingAs($admin)->post('/admin/reference/price-items', ['item' => 'herbicide', 'unit' => 'per_hour'])->assertSessionHasErrors('unit');
    $this->actingAs($admin)->post('/admin/reference/price-items', ['item' => 'herbicide', 'unit' => 'per_kg'])->assertRedirect();

    expect(DB::table('price_items')->where('item', 'herbicide')->value('unit'))->toBe('per_kg');
});

it('edits a row but never its key', function () {
    $admin = referenceAdmin();

    $this->actingAs($admin)->get('/admin/reference/price-units/edit?' . refKey(['value' => 'per_cob']))
        ->assertInertia(fn (Assert $page) => $page->component('Reference/Form')->where('row.label', 'Per cob')->where('rowKey.value', 'per_cob'));

    $this->actingAs($admin)->put('/admin/reference/price-units?' . refKey(['value' => 'per_cob']), ['value' => 'renamed', 'label' => 'Each cob', 'sort_order' => 9])
        ->assertRedirect('/admin/reference/price-units');

    expect(DB::table('price_units')->where('value', 'per_cob')->value('label'))->toBe('Each cob')
        ->and(DB::table('price_units')->where('value', 'renamed')->exists())->toBeFalse();

    $this->actingAs($admin)->get('/admin/reference/price-units/edit?' . refKey(['value' => 'nope']))->assertNotFound();
    $this->actingAs($admin)->get('/admin/reference/price-units/edit')->assertNotFound();
});

it('works with a table whose key is two columns', function () {
    $admin = referenceAdmin();
    $key = ['operation_name' => 'tillage', 'operation_type' => 'tractor'];

    $this->actingAs($admin)->get('/admin/reference/price-choice-operations')
        ->assertInertia(fn (Assert $page) => $page->has('rows', 5));

    $this->actingAs($admin)->put('/admin/reference/price-choice-operations?' . refKey($key), ['item' => 'tractor_harrowing'])->assertRedirect();

    expect(DB::table('price_choice_operations')->where($key)->value('item'))->toBe('tractor_harrowing');

    $this->actingAs($admin)->delete('/admin/reference/price-choice-operations?' . refKey($key))->assertRedirect();

    expect(DB::table('price_choice_operations')->where($key)->exists())->toBeFalse();
});

it('deletes an unused row and explains when a row is still in use', function () {
    $admin = referenceAdmin();

    // per_acre is the unit of several price items
    $this->actingAs($admin)->delete('/admin/reference/price-units?' . refKey(['value' => 'per_acre']))
        ->assertRedirect('/admin/reference/price-units')
        ->assertSessionHas('error');

    expect(DB::table('price_units')->where('value', 'per_acre')->exists())->toBeTrue();

    DB::table('price_units')->insert(['value' => 'per_box', 'label' => 'Per box', 'sort_order' => 9]);

    $this->actingAs($admin)->delete('/admin/reference/price-units?' . refKey(['value' => 'per_box']))->assertSessionHas('success');

    expect(DB::table('price_units')->where('value', 'per_box')->exists())->toBeFalse();
});

it('changes the app price lists when a rule is edited', function () {
    DB::table('countries')->insert(['code' => 'NG', 'name' => 'Nigeria', 'active' => true, 'default_price_basis' => 'open_market']);
    DB::table('default_prices')->insert([
        'country' => 'NG', 'item' => 'cassUP', 'price' => 10000, 'unit' => 'per_tonne', 'currency' => 'NGN',
        'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $before = DB::table('cassava_prices')->where('min_local_price', '>', 0)->max('max_local_price');

    $this->actingAs(referenceAdmin())->put('/admin/reference/price-choice-steps?' . refKey(['step_order' => 4]), ['change_pct' => 50, 'band_half_pct' => 10])
        ->assertRedirect();

    $after = DB::table('cassava_prices')->where('min_local_price', '>', 0)->max('max_local_price');

    expect((float) $before)->toBe(14000.0)->and((float) $after)->toBe(16000.0);
});

it('edits a fertilizer type but never its code, and adds only through the new-fertilizer form', function () {
    $admin = referenceAdmin();
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50]);

    $this->actingAs($admin)->get('/admin/reference/fertilizer-types')
        ->assertInertia(fn (Assert $page) => $page->where('resource.createUrl', '/admin/fertilizer-types/create'));

    $this->actingAs($admin)->get('/admin/reference/fertilizer-types/create')->assertNotFound();

    $this->actingAs($admin)->put('/admin/reference/fertilizer-types?' . refKey(['type' => 'UREA']), ['name' => 'Urea 46%', 'label' => 'urea', 'weight' => 25])
        ->assertRedirect();

    expect(FertilizerType::find('UREA')->name)->toBe('Urea 46%')
        ->and(FertilizerType::find('UREA')->weight)->toBe(25);

    // sold in a country, so it cannot be deleted
    DB::table('country_fertilizers')->insert(['fertilizer_type' => 'UREA', 'country' => 'TZ', 'use_case' => 'FR', 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($admin)->delete('/admin/reference/fertilizer-types?' . refKey(['type' => 'UREA']))->assertSessionHas('error');

    expect(FertilizerType::find('UREA'))->not->toBeNull();
});
