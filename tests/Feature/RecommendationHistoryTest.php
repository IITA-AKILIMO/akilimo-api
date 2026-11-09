<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Support\Facades\Http;

beforeEach(fn () => $this->actingAsApiUser());

it('returns an empty paginated list when no recommendations exist', function () {
    $this->getJson('/api/v1/recommendations')
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(0, 'data');
});

it('returns recommendation records after a compute call', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    $this->getJson('/api/v1/recommendations')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('each record contains expected fields', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    $this->getJson('/api/v1/recommendations')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'request_id', 'device_token'],
            ],
        ]);
});

it('respects the per_page query parameter', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    // Vary field_size so each request gets a distinct cache key and creates its own DB record
    foreach (range(1, 5) as $i) {
        $payload = validComputePayload();
        $payload['compute_request']['farmInformation']['field_size'] = $i;
        $this->postJson('/api/v1/recommendations/compute', $payload);
    }

    $this->getJson('/api/v1/recommendations?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 5);
});

it('paginates correctly with a second page', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    foreach (range(1, 4) as $i) {
        $payload = validComputePayload();
        $payload['compute_request']['farmInformation']['field_size'] = $i;
        $this->postJson('/api/v1/recommendations/compute', $payload);
    }

    $this->getJson('/api/v1/recommendations?per_page=3&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('sorts by created_at descending by default', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload());
    $this->postJson('/api/v1/recommendations/compute', validComputePayload());

    $response = $this->getJson('/api/v1/recommendations?sort=desc')->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->toArray();
    $sorted = collect($ids)->sortDesc()->values()->toArray();

    expect($ids)->toBe($sorted);
});

it('stores the device_token from the request in the history record', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = validComputePayload();
    $deviceToken = $payload['user_info']['device_token'];

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    $this->getJson('/api/v1/recommendations')
        ->assertOk()
        ->assertJsonPath('data.0.device_token', $deviceToken);
});
