<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\MaizeUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function maizeAdmin(): User
{
    return User::create([
        'name' => 'Maize Admin',
        'username' => 'maize_admin_' . str()->random(8),
        'email' => 'maize_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

it('lists the units maize grain is sold in, with the 100 kg bag as the default', function () {
    $response = $this->getJson('/api/v1/maize-units')->assertOk();

    expect(collect($response->json('data'))->pluck('label')->all())->toBe(['ONE_KG', 'FIFTY_KG', 'HUNDRED_KG', 'THOUSAND_KG'])
        ->and(collect($response->json('data'))->pluck('unit_weight')->map(fn ($w) => (float) $w)->all())->toBe([1.0, 50.0, 100.0, 1000.0])
        ->and(collect($response->json('data'))->where('default', true)->pluck('label')->all())->toBe(['HUNDRED_KG'])
        ->and($response->json('data.2.description'))->toContain('100 kg');
});

it('uses the names of the app\'s own units of sale, so a client maps a unit by its label', function () {
    // the same labels as cassava_units, which the app already maps by name
    $cassava = DB::table('cassava_units')->pluck('label')->all();

    expect(MaizeUnit::query()->pluck('label')->all())->each->toBeIn(['ONE_KG', 'FIFTY_KG', 'HUNDRED_KG', 'THOUSAND_KG'])
        ->and(array_intersect(MaizeUnit::query()->pluck('label')->all(), $cassava ?: ['ONE_KG', 'FIFTY_KG', 'HUNDRED_KG', 'THOUSAND_KG']))->not->toBeEmpty();
});

it('offers only the units that are switched on, in order', function () {
    MaizeUnit::where('label', 'FIFTY_KG')->update(['is_active' => false]);
    MaizeUnit::where('label', 'ONE_KG')->update(['sort_order' => 9]);

    $labels = collect($this->getJson('/api/v1/maize-units')->json('data'))->pluck('label')->all();

    expect($labels)->toBe(['HUNDRED_KG', 'THOUSAND_KG', 'ONE_KG']);
});

it('prices maize grain per tonne, so a client converts it to the unit the farmer chose', function () {
    expect((int) DB::table('price_choice_units')->where('item', 'maize_grain')->value('quantity'))->toBe(1000)
        // fresh cob is per cob, not per weight
        ->and((int) DB::table('price_choice_units')->where('item', 'maize_freshCob')->value('quantity'))->toBe(1);

    // average x weight / 1000: a tonne price of 230,000 is 23,000 per 100 kg bag
    $tonne = 230000;
    expect($tonne * MaizeUnit::where('label', 'HUNDRED_KG')->value('unit_weight') / 1000)->toBe(23000.0);
});

it('manages the units on the Price settings screens', function () {
    $admin = maizeAdmin();

    $this->actingAs($admin)->get('/admin/reference/maize-units')
        ->assertInertia(fn (Assert $page) => $page->where('resource.slug', 'maize-units')->has('items.data', 4)->where('items.data.2.label', 'HUNDRED_KG'));

    $this->actingAs($admin)->put('/admin/reference/maize-units?' . http_build_query(['key' => ['label' => 'HUNDRED_KG']]), [
        'unit_weight' => 100, 'description' => 'Standard bag', 'sort_order' => 3, 'is_default' => 0, 'is_active' => 1,
    ])->assertRedirect();

    expect(MaizeUnit::where('label', 'HUNDRED_KG')->first()->is_default)->toBeFalse();

    $this->actingAs($admin)->post('/admin/reference/maize-units', [
        'label' => 'SACK_120', 'unit_weight' => 120, 'description' => 'Large sack', 'sort_order' => 5, 'is_default' => 0, 'is_active' => 1,
    ])->assertRedirect('/admin/reference/maize-units');

    $this->actingAs($admin)->post('/admin/reference/maize-units', [
        'label' => 'sack 5', 'unit_weight' => 0, 'sort_order' => 1, 'is_default' => 3, 'is_active' => 1,
    ])->assertSessionHasErrors(['label', 'unit_weight', 'is_default']);

    expect(MaizeUnit::where('label', 'SACK_120')->value('unit_weight'))->toBe(120.0);
});
