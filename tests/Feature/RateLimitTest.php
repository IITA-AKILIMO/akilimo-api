<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Support\Facades\RateLimiter;

// An empty compute request is rejected with 422 before the compute service is called, which still counts as a hit
it('limits the compute route to the configured requests a minute', function () {
    config(['rate-limits.compute' => 2]);
    RateLimiter::clear('compute|127.0.0.1');

    $this->postJson('/api/v1/recommendations/compute', [])->assertStatus(422);
    $this->postJson('/api/v1/recommendations/compute', [])->assertStatus(422);
    $this->postJson('/api/v1/recommendations/compute', [])->assertStatus(429);
});

it('counts reference lookups apart from compute requests', function () {
    config(['rate-limits.compute' => 1, 'rate-limits.read' => 50]);
    RateLimiter::clear('compute|127.0.0.1');

    foreach (range(1, 5) as $_) {
        $this->getJson('/api/v1/countries')->assertOk();
    }

    $this->postJson('/api/v1/recommendations/compute', [])->assertStatus(422);
});
