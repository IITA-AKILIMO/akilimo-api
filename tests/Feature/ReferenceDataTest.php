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
            ->has('items.data', 5)
            ->where('items.meta.total', 5)
            ->where('items.data.0.value', 'per_bag')
            ->where('items.data.0._key.value', 'per_bag'));
});

it('pages a long table and keeps the filters in the page links', function () {
    for ($i = 1; $i <= 24; $i++) {
        DB::table('price_items')->insert(['item' => sprintf('extra_%02d', $i), 'unit' => 'per_kg']);
    }
    $admin = referenceAdmin();

    $this->actingAs($admin)->get('/admin/reference/price-items?per_page=10')
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 10)
            ->where('items.meta.last_page', fn ($n) => $n >= 4)
            ->where('items.meta.per_page', 10));

    $second = $this->actingAs($admin)->get('/admin/reference/price-items?per_page=10&page=2&unit=per_kg')
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.meta.current_page', 2)
            ->where('items.meta.total', 25)                      // the 24 added and maize_grain, which is per kg too
            ->where('items.links.next', fn ($url) => str_contains((string) $url, 'unit=per_kg')));

    // the page size is held to a sensible range
    $this->actingAs($admin)->get('/admin/reference/price-items?per_page=100000')
        ->assertInertia(fn (Assert $page) => $page->where('items.meta.per_page', 100));
});

it('searches a table and filters it by a choice, offering only values it holds', function () {
    DB::table('price_items')->insert([['item' => 'herbicide_a', 'unit' => 'per_kg'], ['item' => 'herbicide_b', 'unit' => 'per_kg']]);
    $admin = referenceAdmin();

    $this->actingAs($admin)->get('/admin/reference/price-items?search=herbicide')
        ->assertInertia(fn (Assert $page) => $page->where('items.meta.total', 2)->where('filters.search', 'herbicide'));

    $this->actingAs($admin)->get('/admin/reference/price-items?unit=per_kg')
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.meta.total', fn ($n) => $n >= 3)
            ->where('items.data.0.unit', 'per_kg')
            ->where('selectFilters.0.key', 'unit')
            ->where('selectFilters.0.options', fn ($options) => collect($options)->pluck('value')->contains('per_kg')
                && ! collect($options)->pluck('value')->contains('per_litre')));

    $this->actingAs($admin)->get('/admin/reference/price-items?search=zzzz-nothing')
        ->assertInertia(fn (Assert $page) => $page->where('items.meta.total', 0)->has('items.data', 0));
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
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 5));

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

function previewCatalogue(): void
{
    DB::table('countries')->insert(['code' => 'NG', 'name' => 'Nigeria', 'active' => true, 'default_price_basis' => 'open_market']);
    DB::table('default_prices')->insert([
        ['country' => 'NG', 'item' => 'cassUP', 'price' => 10000, 'unit' => 'per_tonne', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ['country' => 'NG', 'item' => 'maize_grain', 'price' => 230, 'unit' => 'per_kg', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ['country' => 'NG', 'item' => 'investment_amount', 'price' => 10000, 'unit' => 'per_acre', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

it('previews what a changed step would do, and saves nothing', function () {
    previewCatalogue();
    $steps = fn () => DB::table('price_choice_steps')->orderBy('step_order')->pluck('change_pct')->map(fn ($v) => (float) $v)->all();
    $maxBefore = DB::table('cassava_prices')->max('max_local_price');

    $response = $this->actingAs(referenceAdmin())
        ->postJson('/admin/reference/price-choice-steps/preview?' . refKey(['step_order' => 4]), ['change_pct' => 50, 'band_half_pct' => 10])
        ->assertOk();

    $cassava = collect($response->json('lists'))->firstWhere('key', 'cassava');

    expect($response->json('changed'))->toBeGreaterThan(0)
        ->and($cassava['changed'])->toBe(1)
        ->and($cassava['rows'][0]['label'])->toBe('NG band 4')
        ->and($cassava['rows'][0]['before'])->toBe('12,000 – 14,000')
        ->and($cassava['rows'][0]['after'])->toBe('14,000 – 16,000')
        ->and($steps())->toBe([-30.0, -10.0, 10.0, 30.0])                       // nothing was saved
        ->and(DB::table('cassava_prices')->max('max_local_price'))->toBe($maxBefore);
});

it('previews what deleting a row would remove, and deletes nothing', function () {
    previewCatalogue();
    $before = DB::table('cassava_prices')->count();

    $response = $this->actingAs(referenceAdmin())
        ->postJson('/admin/reference/price-choice-steps/preview?delete=1&' . refKey(['step_order' => 4]))
        ->assertOk();

    $cassava = collect($response->json('lists'))->firstWhere('key', 'cassava');

    expect($cassava['removed'])->toBe(1)
        ->and($cassava['rows'][0]['label'])->toBe('NG band 4')
        ->and($cassava['rows'][0]['before'])->toBe('12,000 – 14,000')
        ->and($cassava['rows'][0]['after'])->toBeNull()
        ->and(DB::table('price_choice_steps')->count())->toBe(4)                 // nothing was deleted
        ->and(DB::table('cassava_prices')->count())->toBe($before);
});

it('previews deleting an investment multiple and a unit', function () {
    previewCatalogue();
    $admin = referenceAdmin();

    $multiple = $this->actingAs($admin)->postJson('/admin/reference/price-choice-multiples/preview?delete=1&' . refKey(['step_order' => 5]))->assertOk();
    $units = $this->actingAs($admin)->postJson('/admin/reference/price-choice-units/preview?delete=1&' . refKey(['item' => 'maize_grain']))->assertOk();

    expect(collect($multiple->json('lists'))->firstWhere('key', 'investment')['removed'])->toBe(1)
        ->and(collect($units->json('lists'))->firstWhere('key', 'maize')['changed'])->toBeGreaterThan(0)
        ->and(DB::table('price_choice_multiples')->count())->toBe(5)
        ->and(DB::table('price_choice_units')->where('item', 'maize_grain')->exists())->toBeTrue();
});

it('refuses to preview deleting a row that does not exist, or in a table with no price effect', function () {
    $admin = referenceAdmin();

    $this->actingAs($admin)->postJson('/admin/reference/price-choice-steps/preview?delete=1&' . refKey(['step_order' => 99]))->assertNotFound();
    $this->actingAs($admin)->postJson('/admin/reference/price-units/preview?delete=1&' . refKey(['value' => 'per_bag']))->assertNotFound();
});

it('previews a new multiple as new investment rows', function () {
    previewCatalogue();

    $response = $this->actingAs(referenceAdmin())
        ->postJson('/admin/reference/price-choice-multiples/preview', ['step_order' => 6, 'multiple' => 10])
        ->assertOk();

    $investment = collect($response->json('lists'))->firstWhere('key', 'investment');

    expect($investment['added'])->toBe(1)
        ->and($investment['rows'][0]['before'])->toBeNull()
        ->and($investment['rows'][0]['after'])->toBe('100,000')
        ->and(DB::table('price_choice_multiples')->count())->toBe(5);
});

it('previews a changed unit quantity on the maize list only', function () {
    previewCatalogue();

    $response = $this->actingAs(referenceAdmin())
        ->postJson('/admin/reference/price-choice-units/preview?' . refKey(['item' => 'maize_grain']), ['quantity' => 100, 'rounding' => null])
        ->assertOk();

    $changed = collect($response->json('lists'))->filter(fn ($l) => $l['changed'] > 0)->pluck('key')->all();

    expect($changed)->toBe(['maize'])
        ->and((int) DB::table('price_choice_units')->where('item', 'maize_grain')->value('quantity'))->toBe(1000);   // unchanged: per tonne
});

it('says when a change would alter nothing', function () {
    previewCatalogue();

    $this->actingAs(referenceAdmin())
        ->postJson('/admin/reference/price-choice-steps/preview?' . refKey(['step_order' => 4]), ['change_pct' => 30, 'band_half_pct' => 10])
        ->assertOk()
        ->assertJsonPath('changed', 0);
});

it('refuses to preview values that would not save, or a table with no price effect', function () {
    $admin = referenceAdmin();

    $this->actingAs($admin)->postJson('/admin/reference/price-choice-steps/preview?' . refKey(['step_order' => 4]), ['change_pct' => 'lots', 'band_half_pct' => 10])
        ->assertStatus(422)->assertJsonValidationErrors('change_pct');

    $this->actingAs($admin)->postJson('/admin/reference/price-choice-multiples/preview', ['step_order' => 1, 'multiple' => 3])
        ->assertStatus(422)->assertJsonValidationErrors('step_order');

    $this->actingAs($admin)->postJson('/admin/reference/price-units/preview', ['value' => 'per_box', 'label' => 'Box', 'sort_order' => 1])
        ->assertNotFound();
});

it('keeps the preview to admins and tells the form which tables can preview', function () {
    $this->actingAs(referenceAdmin(EnumUserRole::Partner))->postJson('/admin/reference/price-choice-steps/preview?' . refKey(['step_order' => 4]), ['change_pct' => 50, 'band_half_pct' => 10])
        ->assertRedirect();

    $this->actingAs(referenceAdmin())->get('/admin/reference/price-choice-rounding/create')
        ->assertInertia(fn (Assert $page) => $page->where('resource.canPreview', true));

    $this->actingAs(referenceAdmin())->get('/admin/reference/price-units/create')
        ->assertInertia(fn (Assert $page) => $page->where('resource.canPreview', false));
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
