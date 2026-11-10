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
