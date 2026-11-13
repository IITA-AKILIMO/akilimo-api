<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

beforeEach(fn () => $this->actingAsApiUser());

function validFeedbackPayload(): array
{
    return [
        'akilimo_usage' => 'fertilizer recommendation',
        'user_type' => 'farmer',
        'device_token' => fake()->uuid(),
        'device_language' => 'en',
        'satisfaction_rating' => 4,
        'nps_score' => 8,
        'use_case' => 'FR',
    ];
}

it('stores valid feedback and returns the created record', function () {
    $response = $this->postJson('/api/v1/user-feedback', validFeedbackPayload());

    $response->assertCreated()->assertJsonStructure(['id']);
    $this->assertDatabaseCount('user_feedback', 1);
});

it('returns 422 when required feedback fields are missing', function () {
    $this->postJson('/api/v1/user-feedback', [])->assertUnprocessable();
});

it('returns 422 when satisfaction_rating is out of range', function () {
    $payload = validFeedbackPayload();
    $payload['satisfaction_rating'] = 6;

    $this->postJson('/api/v1/user-feedback', $payload)->assertUnprocessable();
});

it('returns 422 when nps_score is out of range', function () {
    $payload = validFeedbackPayload();
    $payload['nps_score'] = 11;

    $this->postJson('/api/v1/user-feedback', $payload)->assertUnprocessable();
});

it('returns 422 when device_language is not supported', function () {
    $payload = validFeedbackPayload();
    $payload['device_language'] = 'de';

    $this->postJson('/api/v1/user-feedback', $payload)->assertUnprocessable();
});

it('returns 422 when akilimo_usage exceeds max length', function () {
    $payload = validFeedbackPayload();
    $payload['akilimo_usage'] = str_repeat('a', 256);

    $this->postJson('/api/v1/user-feedback', $payload)->assertUnprocessable();
});

it('lists feedback with pagination', function () {
    // Create some records via the API
    $this->postJson('/api/v1/user-feedback', validFeedbackPayload());
    $this->postJson('/api/v1/user-feedback', validFeedbackPayload());

    $this->getJson('/api/v1/user-feedback')
        ->assertOk()
        ->assertJsonStructure(['data']);
});
