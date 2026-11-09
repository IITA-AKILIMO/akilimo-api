<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Repositories\ApiRequestRepo;
use App\Repositories\FertilizerRepo;
use App\Service\AkilimoComputeService;
use App\Service\RecommendationService;

function makeService(): RecommendationService
{
    return new RecommendationService(
        Mockery::mock(FertilizerRepo::class),
        Mockery::mock(ApiRequestRepo::class),
        Mockery::mock(AkilimoComputeService::class),
    );
}

function baseRequest(): array
{
    return [
        'user_info' => ['device_token' => 'abc', 'email_address' => 'a@b.com'],
        'compute_request' => ['farmInformation' => ['country_code' => 'NG']],
        'fertilizer_list' => [],
    ];
}

it('produces the same cache key for identical requests', function () {
    $service = makeService();
    $method = new ReflectionMethod($service, 'generateCacheKey');

    $req = baseRequest();
    expect($method->invoke($service, $req))->toBe($method->invoke($service, $req));
});

it('produces different cache keys when fertilizer_list differs', function () {
    $service = makeService();
    $method = new ReflectionMethod($service, 'generateCacheKey');

    $req1 = baseRequest();
    $req2 = baseRequest();
    $req2['fertilizer_list'] = [['key' => 'urea', 'selected' => true, 'weight' => 50, 'price' => 100]];

    expect($method->invoke($service, $req1))->not->toBe($method->invoke($service, $req2));
});

it('produces the same cache key regardless of user_info differences', function () {
    $service = makeService();
    $method = new ReflectionMethod($service, 'generateCacheKey');

    $req1 = baseRequest();
    $req2 = baseRequest();
    $req2['user_info']['email_address'] = 'different@example.com';
    $req2['user_info']['device_token'] = 'different-token';

    expect($method->invoke($service, $req1))->toBe($method->invoke($service, $req2));
});

it('produces different keys when compute_request differs', function () {
    $service = makeService();
    $method = new ReflectionMethod($service, 'generateCacheKey');

    $req1 = baseRequest();
    $req2 = baseRequest();
    $req2['compute_request']['farmInformation']['field_size'] = 5.0;

    expect($method->invoke($service, $req1))->not->toBe($method->invoke($service, $req2));
});
