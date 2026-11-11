<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\CountryFertilizer;
use App\Models\FertilizerType;
use App\Models\User;
use App\Service\FertilizerTypeService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function typeAdmin(): User
{
    return User::create([
        'name' => 'Type Admin',
        'username' => 'type_admin_' . str()->random(8),
        'email' => 'type_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

it('works the type code out from the name', function () {
    $service = app(FertilizerTypeService::class);

    expect($service->codeFor('NPK 20:10:10'))->toBe('NPK_20_10_10')
        ->and($service->codeFor('Urea 46%'))->toBe('UREA_46')
        ->and($service->codeFor('  YaraMila  UNIK '))->toBe('YARAMILA_UNIK')
        ->and($service->codeFor('Sulfató de amonio'))->toBe('SULFATO_DE_AMONIO')
        ->and($service->codeFor('%%%'))->toBe('')
        ->and($service->labelFor('NPK 20:10:10'))->toBe('NPK201010')
        ->and($service->labelFor('Nafaka Plus'))->toBe('NafakaPlus');
});

it('adds a number when a code is taken, whatever the case', function () {
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50]);

    $service = app(FertilizerTypeService::class);

    expect($service->uniqueCode('UREA'))->toBe('UREA_2')
        ->and($service->uniqueCode('DAP'))->toBe('DAP');

    FertilizerType::create(['type' => 'UREA_2', 'label' => 'urea2', 'name' => 'Urea 2', 'weight' => 50]);

    expect($service->uniqueCode('UREA'))->toBe('UREA_3');
});

it('creates a fertiliser with a generated code and makes it priceable', function () {
    $type = app(FertilizerTypeService::class)->create(['name' => 'NPK 20:10:10', 'weight' => 25]);

    expect($type->type)->toBe('NPK_20_10_10')
        ->and($type->label)->toBe('NPK201010')
        ->and($type->weight)->toBe(25)
        ->and(DB::table('price_items')->where('item', 'NPK201010')->value('unit'))->toBe('per_bag');
});

it('keeps a label that was typed, and does not duplicate an existing item', function () {
    DB::table('price_items')->insert(['item' => 'MyLabel', 'unit' => 'per_bag']);

    $type = app(FertilizerTypeService::class)->create(['name' => 'Something', 'weight' => 50, 'label' => 'MyLabel']);

    expect($type->label)->toBe('MyLabel')
        ->and(DB::table('price_items')->where('item', 'MyLabel')->count())->toBe(1);
});

it('adds a fertiliser from the admin form and goes on to choose its countries', function () {
    $admin = typeAdmin();

    $this->actingAs($admin)->get('/admin/fertilizer-types/create')
        ->assertInertia(fn (Assert $page) => $page->component('FertilizerTypes/Create'));

    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => 'Urea 46%', 'weight' => 50])
        ->assertRedirect('/admin/fertilizers/create');

    expect(FertilizerType::find('UREA_46')?->name)->toBe('Urea 46%');
});

it('refuses a name with no letters or digits, and a label another fertiliser uses', function () {
    $admin = typeAdmin();
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50]);

    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => '%%%', 'weight' => 50])->assertSessionHasErrors('name');
    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => 'Other', 'weight' => 50, 'label' => 'urea'])->assertSessionHasErrors('label');
    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => 'Other', 'weight' => 0])->assertSessionHasErrors('weight');

    expect(FertilizerType::count())->toBe(1);
});

it('offers the use cases as a list and accepts only those', function () {
    $admin = typeAdmin();
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50]);

    $this->actingAs($admin)->get('/admin/fertilizers/create')
        ->assertInertia(fn (Assert $page) => $page
            ->has('useCases', 4)
            ->where('useCases.0.value', 'FR'));

    $this->actingAs($admin)->post('/admin/fertilizers', ['type' => 'UREA', 'country' => 'TZ', 'use_case' => 'MAIZE'])
        ->assertSessionHasErrors('use_case');

    // saved with the list's spelling
    $this->actingAs($admin)->post('/admin/fertilizers', ['type' => 'UREA', 'country' => 'TZ', 'use_case' => 'fr', 'available' => true])
        ->assertRedirect('/admin/fertilizers');

    expect(CountryFertilizer::where('country', 'TZ')->value('use_case'))->toBe('FR');
});

it('makes the database refuse a use case that is not in the list', function () {
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50]);

    expect(fn () => CountryFertilizer::create(['fertilizer_type' => 'UREA', 'country' => 'TZ', 'use_case' => 'MAIZE']))
        ->toThrow(QueryException::class);
});


it('classifies a fertiliser by category, defaulting to OTHER, and refuses an unknown category', function () {
    $admin = typeAdmin();

    $this->actingAs($admin)->get('/admin/fertilizer-types/create')
        ->assertInertia(fn (Assert $page) => $page->has('categories', 3)->where('categories.0.value', 'STRAIGHT'));

    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => 'Urea 46%', 'weight' => 50, 'category' => 'STRAIGHT'])
        ->assertSessionHasNoErrors();
    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => 'Lime', 'weight' => 50])
        ->assertSessionHasNoErrors();
    $this->actingAs($admin)->post('/admin/fertilizer-types', ['name' => 'Mystery', 'weight' => 50, 'category' => 'MAGIC'])
        ->assertSessionHasErrors('category');

    expect(FertilizerType::find('UREA_46')->category)->toBe('STRAIGHT')
        ->and(FertilizerType::find('LIME')->category)->toBe('OTHER');
});

it('shows the category in the fertilizers view and the API', function () {
    $type = FertilizerType::create(['type' => 'DAP', 'label' => 'DAP', 'name' => 'DAP', 'weight' => 50, 'category' => 'COMPOUND']);
    CountryFertilizer::create(['fertilizer_type' => $type->type, 'country' => 'NG', 'use_case' => 'FR', 'available' => true]);

    DB::table('default_prices')->insert(['country' => 'NG', 'item' => 'DAP', 'fertilizer_type' => 'DAP', 'price' => 9000, 'unit' => 'per_bag', 'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

    expect(DB::table('fertilizers')->where('type', 'DAP')->value('category'))->toBe('COMPOUND');

    $this->getJson('/api/v1/fertilizers/country/NG')->assertOk()
        ->assertJsonPath('data.0.category', 'COMPOUND');
});

it('does not let a category that fertilisers use be removed', function () {
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50, 'category' => 'STRAIGHT']);

    expect(fn () => DB::table('fertilizer_categories')->where('value', 'STRAIGHT')->delete())->toThrow(QueryException::class);
});


it('edits a fertiliser itself: name, weight and category, but never the code or label', function () {
    $admin = typeAdmin();
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50, 'category' => 'OTHER']);
    CountryFertilizer::create(['fertilizer_type' => 'UREA', 'country' => 'NG', 'use_case' => 'FR', 'available' => true]);

    $this->actingAs($admin)->get('/admin/fertilizer-types/UREA/edit')
        ->assertInertia(fn (Assert $page) => $page->component('FertilizerTypes/Edit')->where('item.type', 'UREA')->where('countries', ['NG'])->has('categories', 3));

    $this->actingAs($admin)->put('/admin/fertilizer-types/UREA', ['name' => 'Urea 46%', 'weight' => 25, 'category' => 'STRAIGHT', 'type' => 'HACK', 'label' => 'hack'])
        ->assertSessionHasNoErrors();

    $type = FertilizerType::find('UREA');
    expect($type->name)->toBe('Urea 46%')->and($type->weight)->toBe(25)->and($type->category)->toBe('STRAIGHT')->and($type->label)->toBe('urea');

    $this->actingAs($admin)->put('/admin/fertilizer-types/UREA', ['name' => 'Urea', 'weight' => 25, 'category' => 'MAGIC'])->assertSessionHasErrors('category');
});

it('saves the category picked on the country form on the fertiliser', function () {
    $admin = typeAdmin();
    FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50, 'category' => 'OTHER']);
    $row = CountryFertilizer::create(['fertilizer_type' => 'UREA', 'country' => 'NG', 'use_case' => 'FR', 'available' => true]);

    $this->actingAs($admin)->get("/admin/fertilizers/{$row->id}/edit")
        ->assertInertia(fn (Assert $page) => $page->has('categories', 3)->where('item.category', 'OTHER'));

    $this->actingAs($admin)->put("/admin/fertilizers/{$row->id}", ['category' => 'STRAIGHT'])->assertSessionHasNoErrors();

    expect(FertilizerType::find('UREA')->category)->toBe('STRAIGHT');
});


it('lists only the fertilizers that have an active price in their country', function () {
    $priced = FertilizerType::create(['type' => 'UREA', 'label' => 'urea', 'name' => 'Urea', 'weight' => 50, 'category' => 'STRAIGHT']);
    $unpriced = FertilizerType::create(['type' => 'MOP', 'label' => 'MOP', 'name' => 'MOP', 'weight' => 50, 'category' => 'STRAIGHT']);
    $draft = FertilizerType::create(['type' => 'TSP', 'label' => 'TSP', 'name' => 'TSP', 'weight' => 50, 'category' => 'STRAIGHT']);

    foreach ([$priced, $unpriced, $draft] as $type) {
        CountryFertilizer::create(['fertilizer_type' => $type->type, 'country' => 'NG', 'use_case' => 'FR', 'available' => true]);
    }

    $price = fn (string $item, string $type, string $status) => DB::table('default_prices')->insert([
        'country' => 'NG', 'item' => $item, 'fertilizer_type' => $type, 'price' => 9000, 'unit' => 'per_bag',
        'currency' => 'NGN', 'status' => $status, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $price('urea', 'UREA', 'active');
    $price('TSP', 'TSP', 'draft');

    $this->getJson('/api/v1/fertilizers/country/NG')->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'UREA');
    $this->getJson('/api/v1/fertilizers')->assertOk()->assertJsonCount(1, 'data');

    // The admin list still shows every fertilizer, so one without a price can be found and fixed
    expect(DB::table('fertilizers')->where('country', 'NG')->count())->toBe(3);

    // Activating the price lists the fertilizer
    DB::table('default_prices')->where('item', 'TSP')->update(['status' => 'active']);
    $this->getJson('/api/v1/fertilizers/country/NG')->assertJsonCount(2, 'data');
});
