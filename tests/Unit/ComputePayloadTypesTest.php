<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => Cache::flush());

it('sends the yield class with its decimals', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);
    $payload = validComputePayload();
    $payload['compute_request']['yieldInfo']['current_field_yield'] = 11.25;

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    Http::assertSent(fn ($request) => $request['FCY'] === 11.25);
});

it('sends the farm name, and null instead of leaving it out when there is none', function (?string $name) {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);
    $payload = validComputePayload();
    $payload['user_info']['farm_name'] = $name;

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    Http::assertSent(fn ($request) => array_key_exists('userField', $request->data()) && $request['userField'] === $name);
})->with(['named' => 'Riverside plot', 'none' => null]);
