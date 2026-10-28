<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\CoverageArea;
use App\Service\CoverageService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

/** A small supported area around Ibadan (lon 3 to 5, lat 7 to 8) in the shape the compute service sends. */
function coverageCollection(float $lonMax = 5.0): array
{
    return [
        'type' => 'FeatureCollection',
        'features' => [[
            'type' => 'Feature',
            'properties' => ['country' => 'NG', 'n_cells' => 1600],
            'geometry' => ['type' => 'Polygon', 'coordinates' => [[[3.0, 7.0], [$lonMax, 7.0], [$lonMax, 8.0], [3.0, 8.0], [3.0, 7.0]]]],
        ]],
    ];
}

function fakeComputeCoverage(?float $lonMax = 5.0): void
{
    // A fresh client each time: stubs registered earlier would otherwise keep answering first
    Http::swap(new Factory);
    Http::fake([
        '*/coverage/NG' => Http::response(coverageCollection($lonMax), 200, ['Content-Type' => 'application/geo+json']),
        '*/coverage/*' => Http::response(['status' => 'error'], 500),
    ]);
}

function seedCoverage(): void
{
    fakeComputeCoverage();
    app(CoverageService::class)->sync(['NG']);
}

// ── ingest ───────────────────────────────────────────────────────────────────

it('copies the compute service coverage into the table', function () {
    fakeComputeCoverage();

    $results = app(CoverageService::class)->sync(['NG']);

    expect($results['NG']['status'])->toBe('created');
    $area = CoverageArea::where('country_code', 'NG')->firstOrFail();
    expect($area->n_cells)->toBe(1600)
        ->and([$area->min_lon, $area->min_lat, $area->max_lon, $area->max_lat])->toBe([3.0, 7.0, 5.0, 8.0])
        ->and(json_decode($area->geojson, true)['type'])->toBe('FeatureCollection')
        ->and($area->checksum)->toHaveLength(64);
});

it('leaves an unchanged area alone and updates a changed one', function () {
    fakeComputeCoverage();
    app(CoverageService::class)->sync(['NG']);

    expect(app(CoverageService::class)->sync(['NG'])['NG']['status'])->toBe('unchanged');

    fakeComputeCoverage(lonMax: 6.0);
    expect(app(CoverageService::class)->sync(['NG'])['NG']['status'])->toBe('updated');
    expect(CoverageArea::where('country_code', 'NG')->count())->toBe(1)
        ->and(CoverageArea::first()->max_lon)->toBe(6.0);
});

it('reports a failing country without losing the others', function () {
    fakeComputeCoverage();

    $results = app(CoverageService::class)->sync(['NG', 'RW']);

    expect($results['NG']['status'])->toBe('created')
        ->and($results['RW']['status'])->toBe('failed');
    expect(CoverageArea::count())->toBe(1);
});

it('runs from the coverage:sync command', function () {
    fakeComputeCoverage();

    $this->artisan('coverage:sync', ['country' => ['NG']])->assertExitCode(0);
    expect(CoverageArea::count())->toBe(1);

    $this->artisan('coverage:sync', ['country' => ['RW']])->assertExitCode(1);
});

// ── point check ──────────────────────────────────────────────────────────────

it('says whether a point is inside the supported area', function () {
    seedCoverage();
    $service = app(CoverageService::class);

    expect($service->contains('NG', 7.5, 4.0))->toBeTrue()
        ->and($service->contains('NG', 7.5, 6.0))->toBeFalse()   // east of the area
        ->and($service->contains('NG', 9.5, 4.0))->toBeFalse()   // north of the area
        ->and($service->contains('ng', 7.5, 4.0))->toBeTrue()
        ->and($service->contains('RW', -1.9, 30.0))->toBeNull(); // nothing synced for Rwanda
});

// ── endpoints ────────────────────────────────────────────────────────────────

it('lists the countries that have a supported area', function () {
    seedCoverage();

    $this->getJson('/api/v1/coverage')
        ->assertOk()
        ->assertJsonPath('data.0.country_code', 'NG')
        ->assertJsonPath('data.0.bbox', [3, 7, 5, 8])
        ->assertJsonMissingPath('data.0.geojson');
});

it('serves one country as GeoJSON and 404s an unknown one', function () {
    seedCoverage();

    $response = $this->get('/api/v1/coverage/ng')->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/geo+json')
        ->and(json_decode($response->getContent(), true)['features'][0]['properties']['country'])->toBe('NG');

    $this->getJson('/api/v1/coverage/RW')->assertNotFound();
});

it('serves every country in one FeatureCollection', function () {
    seedCoverage();

    $body = $this->getJson('/api/v1/coverage/geojson')->assertOk()->json();

    expect($body['type'])->toBe('FeatureCollection')->and($body['features'])->toHaveCount(1);
});

it('checks a location through the API', function () {
    seedCoverage();

    $this->getJson('/api/v1/coverage/check?country=NG&lat=7.5&lon=4')->assertOk()->assertJsonPath('data.covered', true);
    $this->getJson('/api/v1/coverage/check?country=NG&lat=7.5&lon=6')->assertOk()->assertJsonPath('data.covered', false);
    $this->getJson('/api/v1/coverage/check?country=RW&lat=-1.9&lon=30')->assertOk()->assertJsonPath('data.covered', null);
    $this->getJson('/api/v1/coverage/check?country=NG&lat=99&lon=4')->assertStatus(422);
});

// ── early validation of compute requests ─────────────────────────────────────

it('refuses a compute request for a location outside the supported area', function () {
    seedCoverage();
    $this->actingAsApiUser();
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = validComputePayload();
    $payload['compute_request']['farmInformation']['map_lat'] = 7.5;
    $payload['compute_request']['farmInformation']['map_long'] = 6.0;

    $this->postJson('/api/v1/recommendations/compute', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['compute_request.farmInformation.map_lat']);

    // and the compute service was never called
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'compute'));
});

it('lets a location inside the supported area through', function () {
    seedCoverage();
    $this->actingAsApiUser();
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = validComputePayload();
    $payload['compute_request']['farmInformation']['map_lat'] = 7.5;
    $payload['compute_request']['farmInformation']['map_long'] = 4.0;

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});

it('does not refuse anything for a country whose area has not been synced', function () {
    $this->actingAsApiUser();
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();
});

it('can switch the early check off', function () {
    seedCoverage();
    config(['akilimo-compute.coverage.validate_requests' => false]);
    $this->actingAsApiUser();
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $payload = validComputePayload();
    $payload['compute_request']['farmInformation']['map_long'] = 6.0;

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
});
