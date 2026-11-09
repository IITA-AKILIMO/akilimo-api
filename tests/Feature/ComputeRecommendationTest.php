<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\ApiRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => $this->actingAsApiUser());

it('returns a recommendation on a successful Plumbr response', function () {
    Http::fake([
        '*' => Http::response(plumberSuccessResponse(), 200),
    ]);

    $response = $this->postJson('/api/v1/recommendations/compute', validComputePayload());

    $response->assertOk()
        ->assertJsonStructure([
            'request_id',
            'status',
            'version',
            'data' => ['rec_type', 'recommendation', 'data', 'fertilizer_rates'],
        ]);
});

it('includes a server-generated request_id in the response', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $response = $this->postJson('/api/v1/recommendations/compute', validComputePayload());

    $response->assertOk();
    $requestId = $response->json('request_id');
    expect($requestId)->toBeString()->not->toBeEmpty();
    // Each call must produce a unique ID
    $response2 = $this->postJson('/api/v1/recommendations/compute', validComputePayload());
    expect($response2->json('request_id'))->not->toBe($requestId);
});

it('logs the request to api_requests with device_token and duration', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = validComputePayload();
    $payload['user_info']['device_token'] = $deviceToken = fake()->uuid();

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    $this->assertDatabaseHas('api_requests', [
        'device_token' => $deviceToken,
    ]);

    $record = ApiRequest::where('device_token', $deviceToken)->first();
    expect($record)->not->toBeNull()
        ->and($record->request_duration_ms)->toBeInt()->toBeGreaterThanOrEqual(0);
});

it('returns 503 when Plumbr is unreachable', function () {
    Http::fake(['*' => fn () => throw new ConnectionException('timeout')]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())
        ->assertStatus(503);
});

it('returns an error when Plumbr responds with a non-2xx status', function () {
    Http::fake(['*' => Http::response(['message' => 'Bad input'], 422)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())
        ->assertStatus(422);
});

it('returns 422 when required fields are missing', function () {
    $this->postJson('/api/v1/recommendations/compute', [])
        ->assertUnprocessable();
});

it('serves subsequent identical requests from cache without calling Plumbr again', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);
    Cache::flush();

    $payload = validComputePayload();
    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    Http::assertSentCount(1);
});

it('does not share cache between requests with different fertilizer selections', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);
    Cache::flush();

    $payload1 = validComputePayload();
    $payload2 = validComputePayload();
    $payload2['fertilizer_list'] = [
        [
            'name' => 'Urea',
            'fertilizer_type' => 'STRAIGHT',
            'key' => 'urea',
            'weight' => 50,
            'price' => 12000,
            'selected' => true,
        ],
    ];

    $this->postJson('/api/v1/recommendations/compute', $payload1)->assertOk();
    $this->postJson('/api/v1/recommendations/compute', $payload2)->assertOk();

    Http::assertSentCount(2);
});
