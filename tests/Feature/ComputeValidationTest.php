<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(\Database\Seeders\FertilizerSeeder::class);
    $this->actingAsApiUser();
});

// ---------------------------------------------------------------------------
// Shared helper — different name to avoid collision with ComputeRecommendationTest
// ---------------------------------------------------------------------------

function computePayload(): array
{
    return validComputePayload();
}

// ---------------------------------------------------------------------------
// area_unit — case-insensitive acceptance
// ---------------------------------------------------------------------------

it('accepts area_unit in uppercase', function (string $unit) {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = computePayload();
    $payload['compute_request']['farmInformation']['area_unit'] = strtoupper($unit);

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
})->with(['ha', 'acre', 'm2', 'are']);

it('accepts area_unit in mixed case', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = computePayload();
    $payload['compute_request']['farmInformation']['area_unit'] = 'Acre';

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});

it('rejects an unknown area_unit value', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['area_unit'] = 'miles';

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.area_unit']);
});

// ---------------------------------------------------------------------------
// GPS coordinates — boundary validation
// ---------------------------------------------------------------------------

it('rejects latitude above 90', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['map_lat'] = 90.1;

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.map_lat']);
});

it('rejects latitude below -90', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['map_lat'] = -90.1;

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.map_lat']);
});

it('rejects longitude above 180', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['map_long'] = 180.1;

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.map_long']);
});

it('rejects longitude below -180', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['map_long'] = -180.1;

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.map_long']);
});

// ---------------------------------------------------------------------------
// field_size — minimum 1
// ---------------------------------------------------------------------------

it('rejects field_size below 1', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['field_size'] = 0.5;

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.field_size']);
});

it('accepts field_size of exactly 1', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = computePayload();
    $payload['compute_request']['farmInformation']['field_size'] = 1;

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});

// ---------------------------------------------------------------------------
// country_code — must be exactly 2 characters
// ---------------------------------------------------------------------------

it('rejects country_code longer than 2 characters', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['country_code'] = 'NGA';

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.country_code']);
});

it('rejects a single-character country_code', function () {
    $payload = computePayload();
    $payload['compute_request']['farmInformation']['country_code'] = 'N';

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['compute_request.farmInformation.country_code']);
});

// ---------------------------------------------------------------------------
// user_info — UUID and gender
// ---------------------------------------------------------------------------

it('rejects a non-UUID device_token', function () {
    $payload = computePayload();
    $payload['user_info']['device_token'] = 'not-a-uuid';

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_info.device_token']);
});

it('rejects an invalid gender value', function () {
    $payload = computePayload();
    $payload['user_info']['gender'] = 'X';

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_info.gender']);
});

it('accepts gender M', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = computePayload();
    $payload['user_info']['gender'] = 'M';

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});

it('accepts gender F', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = computePayload();
    $payload['user_info']['gender'] = 'F';

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});

// ---------------------------------------------------------------------------
// fertilizer_list — must be present, items must have required fields
// ---------------------------------------------------------------------------

it('rejects a request with no fertilizer_list key', function () {
    $payload = computePayload();
    unset($payload['fertilizer_list']);

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['fertilizer_list']);
});

it('accepts an empty fertilizer_list array', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = computePayload();
    $payload['fertilizer_list'] = [];

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});

it('rejects a fertilizer entry missing required fields', function () {
    $payload = computePayload();
    $payload['fertilizer_list'] = [
        ['name' => 'Urea'],  // missing key, fertilizer_type, weight, price, selected
    ];

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable();
});

it('rejects a fertilizer with negative price', function () {
    $payload = computePayload();
    $payload['fertilizer_list'] = [
        [
            'name' => 'Urea',
            'fertilizer_type' => 'STRAIGHT',
            'key' => 'urea',
            'weight' => 50,
            'price' => -1,
            'selected' => true,
        ],
    ];

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['fertilizer_list.0.price']);
});

it('rejects a fertilizer with negative weight', function () {
    $payload = computePayload();
    $payload['fertilizer_list'] = [
        [
            'name' => 'Urea',
            'fertilizer_type' => 'STRAIGHT',
            'key' => 'urea',
            'weight' => -5,
            'price' => 1000,
            'selected' => true,
        ],
    ];

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['fertilizer_list.0.weight']);
});
