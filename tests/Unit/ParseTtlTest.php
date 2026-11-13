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
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

function makeTtlService(): RecommendationService
{
    return new RecommendationService(
        Mockery::mock(FertilizerRepo::class),
        Mockery::mock(ApiRequestRepo::class),
        Mockery::mock(AkilimoComputeService::class),
    );
}

/**
 * Invoke the private parseTtl method and return the resulting Carbon instance.
 */
function parseTtl(string $value): Carbon
{
    $service = makeTtlService();
    $method = new ReflectionMethod($service, 'parseTtl');

    return $method->invoke($service, $value);
}

/**
 * Assert that $result is approximately $expectedSeconds in the future (±5 s tolerance).
 */
function assertApproxSeconds(Carbon $result, int $expectedSeconds): void
{
    $diff = (int)now()->diffInSeconds($result, false);
    expect($diff)->toBeGreaterThanOrEqual($expectedSeconds - 5)
        ->toBeLessThanOrEqual($expectedSeconds + 5);
}

it('parses a days TTL correctly', function () {
    assertApproxSeconds(parseTtl('2d'), 2 * 86400);
});

it('parses an hours TTL correctly', function () {
    assertApproxSeconds(parseTtl('3h'), 3 * 3600);
});

it('parses a minutes TTL correctly', function () {
    assertApproxSeconds(parseTtl('30m'), 30 * 60);
});

it('parses a numeric TTL as seconds', function () {
    assertApproxSeconds(parseTtl('3600'), 3600);
});

it('falls back to 1 hour and logs a warning for an unrecognised TTL', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Unrecognised CACHE_TTL value, defaulting to 1 hour', ['value' => 'invalid']);

    assertApproxSeconds(parseTtl('invalid'), 3600);
});
