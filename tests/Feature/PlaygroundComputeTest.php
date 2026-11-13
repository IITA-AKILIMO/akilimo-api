<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// The compute service needs a fertilizer catalogue for the country; migrations no longer seed one
beforeEach(function () {
    $this->seed(\Database\Seeders\FertilizerSeeder::class);
});

function playgroundUser(): User
{
    return User::create([
        'name' => 'Playground User',
        'username' => 'pg_' . Str::random(6),
        'email' => 'pg_' . Str::random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => EnumUserRole::User,
    ]);
}

/** What the playground form posts for a fertilizer request in Nigeria. */
function playgroundPayload(array $overrides = []): array
{
    return array_merge([
        'country_code' => 'NG',
        'use_case' => 'FR',
        'field_size' => 1,
        'area_unit' => 'ha',
        'map_lat' => 7.3451,
        'map_long' => 6.966,
        'lang' => 'en',
        'field_yield' => 10,
        'soil_quality' => 3,
        'risk_attitude' => 1,
        'cassava_produce_type' => 'roots',
        'cassava_unit_price' => 750,
        'cassava_unit_weight' => 50,
        'maize_produce_type' => 'fresh_cob',
        'maize_unit_price' => null,
        'sweet_potato_produce_type' => 'tubers',
        'sweet_potato_unit_price' => null,
        'fertilizer_rec' => true,
        'planting_practices_rec' => false,
        'scheduled_planting_rec' => false,
        'scheduled_harvest_rec' => false,
        'inter_cropping_maize_rec' => false,
        'inter_cropping_potato_rec' => false,
        'planting_date' => now()->addMonth()->toDateString(),
        'harvest_date' => now()->addMonths(13)->toDateString(),
        'planting_date_window' => 0,
        'harvest_date_window' => 0,
        'fertilizer_list' => [
            ['key' => 'UREA_NG', 'name' => 'UREA', 'fertilizer_type' => 'UREA', 'weight' => 50, 'price' => 28000, 'selected' => true],
        ],
    ], $overrides);
}

it('sends the cassava price and selling unit to the compute service', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->actingAs(playgroundUser())
        ->postJson('/playground/compute', playgroundPayload())
        ->assertOk();

    // Before, the playground always sent a price of 0, so fertilizer could never pay
    Http::assertSent(fn (HttpRequest $r) => (float)$r['cassUP'] === 750.0 && (float)$r['cassUW'] === 50.0);
});

it('refuses a request without a cassava price', function () {
    Http::fake();

    $this->actingAs(playgroundUser())
        ->postJson('/playground/compute', playgroundPayload(['cassava_unit_price' => 0]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cassava_unit_price');

    Http::assertNothingSent();
});

it('needs the maize price only when maize intercropping is asked for', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);
    $user = playgroundUser();

    $this->actingAs($user)
        ->postJson('/playground/compute', playgroundPayload(['use_case' => 'IC', 'fertilizer_rec' => false, 'inter_cropping_maize_rec' => true]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('maize_unit_price');

    $this->actingAs($user)
        ->postJson('/playground/compute', playgroundPayload([
            'use_case' => 'IC', 'fertilizer_rec' => false, 'inter_cropping_maize_rec' => true, 'maize_unit_price' => 15,
        ]))
        ->assertOk();

    Http::assertSent(fn (HttpRequest $r) => (float)($r['maizeUP'] ?? -1) === 15.0);
});
