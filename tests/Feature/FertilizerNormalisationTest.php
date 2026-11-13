<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\CountryFertilizer;
use App\Models\Fertilizer;
use App\Models\FertilizerType;
use App\Models\User;
use App\Repositories\FertilizerRepo;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function fertilizerAdmin(): User
{
    return User::create([
        'name' => 'Fertilizer Admin',
        'username' => 'fert_admin_' . str()->random(8),
        'email' => 'fert_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

function fertilizerType(string $type = 'UREA', string $label = 'urea', string $name = 'Urea', int $weight = 50): FertilizerType
{
    return FertilizerType::create(['type' => $type, 'label' => $label, 'name' => $name, 'weight' => $weight]);
}

function soldIn(FertilizerType $type, string $country, array $overrides = []): CountryFertilizer
{
    return CountryFertilizer::create(array_merge([
        'fertilizer_type' => $type->type,
        'country' => $country,
        'use_case' => 'FR',
        'cis' => true,
        'cim' => false,
        'available' => true,
        'sort_order' => 1,
    ], $overrides));
}

it('stores a fertiliser once and the countries that sell it separately', function () {
    $urea = fertilizerType();
    soldIn($urea, 'TZ');
    soldIn($urea, 'NG');

    expect(FertilizerType::count())->toBe(1)
        ->and(CountryFertilizer::count())->toBe(2);
});

it('uses the type code as the key and follows a rename', function () {
    $urea = fertilizerType();
    $row = soldIn($urea, 'TZ');
    $priceId = DB::table('price_catalogue')->insertGetId([
        'country' => 'TZ', 'item' => 'urea', 'fertilizer_type' => 'UREA', 'price' => 50000, 'unit' => 'per_bag',
        'currency' => 'TZS', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::table('fertilizer_types')->where('type', 'UREA')->update(['type' => 'UREA_46']);

    expect(FertilizerType::find('UREA_46')->name)->toBe('Urea')
        ->and(CountryFertilizer::find($row->id)->fertilizer_type)->toBe('UREA_46')
        ->and(Fertilizer::find($row->id)->fertilizer_key)->toBe('UREA_46_TZ')
        ->and(DB::table('price_catalogue')->where('id', $priceId)->value('fertilizer_type'))->toBe('UREA_46');
});

it('refuses a catalogue price for a fertiliser that does not exist, or removing one that has prices', function () {
    $urea = fertilizerType();
    soldIn($urea, 'TZ');

    expect(fn () => DB::table('price_catalogue')->insert([
        'country' => 'TZ', 'item' => 'mystery', 'fertilizer_type' => 'NOPE', 'price' => 1, 'unit' => 'per_bag',
        'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(QueryException::class);

    DB::table('price_catalogue')->insert([
        'country' => 'TZ', 'item' => 'urea', 'fertilizer_type' => 'UREA', 'price' => 50000, 'unit' => 'per_bag',
        'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('country_fertilizers')->delete();

    expect(fn () => DB::table('fertilizer_types')->where('type', 'UREA')->delete())->toThrow(QueryException::class);
});

it('does not let a fertiliser be sold twice in one country', function () {
    $urea = fertilizerType();
    soldIn($urea, 'TZ');

    expect(fn () => soldIn($urea, 'TZ'))->toThrow(QueryException::class);
});

it('serves the fertilizers view with the old columns and a computed key', function () {
    $npk = fertilizerType('NPK_20_10_10', 'NPK201010', 'NPK 20:10:10', 25);
    $row = soldIn($npk, 'GH', ['use_case' => 'CIM', 'cis' => false, 'cim' => true, 'available' => false, 'sort_order' => 4]);

    $fertilizer = Fertilizer::find($row->id);

    expect($fertilizer->fertilizer_key)->toBe('NPK_20_10_10_GH')
        ->and($fertilizer->fertilizer_label)->toBe('NPK201010')
        ->and($fertilizer->name)->toBe('NPK 20:10:10')
        ->and($fertilizer->type)->toBe('NPK_20_10_10')
        ->and($fertilizer->weight)->toBe(25)
        ->and($fertilizer->country)->toBe('GH')
        ->and($fertilizer->use_case)->toBe('CIM')
        ->and($fertilizer->cim)->toBeTrue()
        ->and($fertilizer->cis)->toBeFalse()
        ->and($fertilizer->available)->toBeFalse()
        ->and($fertilizer->sort_order)->toBe(4);
});

it('changes a fertiliser name for every country in one place', function () {
    $urea = fertilizerType();
    $tz = soldIn($urea, 'TZ');
    $ng = soldIn($urea, 'NG');

    $urea->update(['name' => 'Urea 46%', 'weight' => 25]);

    expect(Fertilizer::find($tz->id)->name)->toBe('Urea 46%')
        ->and(Fertilizer::find($ng->id)->weight)->toBe(25);
});

it('sells a fertiliser in a country through the repository', function () {
    fertilizerType();

    $created = app(FertilizerRepo::class)->create(['type' => 'UREA', 'country' => 'RW', 'use_case' => 'FR', 'cis' => true, 'cim' => true, 'available' => true, 'sort_order' => 2]);

    expect($created->fertilizer_key)->toBe('UREA_RW')
        ->and($created->name)->toBe('Urea')
        ->and(CountryFertilizer::where('country', 'RW')->count())->toBe(1);
});

it('updates a country row and moves it to another fertiliser type', function () {
    $urea = fertilizerType();
    fertilizerType('DAP', 'DAP', 'DAP');
    $row = soldIn($urea, 'TZ');

    $updated = app(FertilizerRepo::class)->update($row->id, ['type' => 'DAP', 'available' => false]);

    expect($updated->fertilizer_key)->toBe('DAP_TZ')
        ->and($updated->available)->toBeFalse();
});

it('stops selling in a country without removing the fertiliser', function () {
    $urea = fertilizerType();
    $row = soldIn($urea, 'TZ');
    soldIn($urea, 'NG');

    app(FertilizerRepo::class)->delete($row->id);

    expect(CountryFertilizer::where('country', 'TZ')->count())->toBe(0)
        ->and(CountryFertilizer::where('country', 'NG')->count())->toBe(1)
        ->and(FertilizerType::count())->toBe(1);
});

it('is read-only through the fertilizers view', function () {
    fertilizerType();

    $row = ['fertilizer_label' => 'x', 'name' => 'x', 'type' => 'X', 'fertilizer_key' => 'X_TZ', 'weight' => 50, 'country' => 'TZ', 'use_case' => 'FR'];

    // The model refuses mass assignment, and the database refuses a direct write to the view
    expect(fn () => Fertilizer::query()->create($row))->toThrow(MassAssignmentException::class)
        ->and(fn () => DB::table('fertilizers')->insert($row))->toThrow(QueryException::class);
});

it('gives the admin form the fertiliser list and refuses an unknown type', function () {
    fertilizerType();
    $admin = fertilizerAdmin();

    $this->actingAs($admin)->get('/admin/fertilizers/create')
        ->assertInertia(fn (Assert $page) => $page->component('Fertilizers/Create')->has('types', 1)->where('types.0.type', 'UREA'));

    $this->actingAs($admin)->post('/admin/fertilizers', ['type' => 'STRAIGHT', 'country' => 'TZ', 'use_case' => 'FR'])
        ->assertSessionHasErrors('type');

    $this->actingAs($admin)->post('/admin/fertilizers', ['type' => 'urea', 'country' => 'TZ', 'use_case' => 'FR', 'available' => true])
        ->assertRedirect('/admin/fertilizers');

    // saved with the spelling in the lookup, and the key is worked out
    expect(DB::table('fertilizers')->where('country', 'TZ')->value('fertilizer_key'))->toBe('UREA_TZ');
});
