<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

use App\Support\GeoJson;

function squareFeature(float $x0, float $y0, float $x1, float $y1): array
{
    return [
        'type' => 'Feature',
        'properties' => ['country' => 'XX'],
        'geometry' => ['type' => 'Polygon', 'coordinates' => [[[$x0, $y0], [$x1, $y0], [$x1, $y1], [$x0, $y1], [$x0, $y0]]]],
    ];
}

function collectionOf(array ...$features): array
{
    return ['type' => 'FeatureCollection', 'features' => $features];
}

it('finds a point inside a polygon and rejects one outside', function () {
    $area = collectionOf(squareFeature(29.0, -3.0, 31.0, -1.0));

    expect(GeoJson::contains($area, lat: -2.0, lon: 30.0))->toBeTrue()
        ->and(GeoJson::contains($area, lat: -2.0, lon: 32.0))->toBeFalse()
        ->and(GeoJson::contains($area, lat: 0.5, lon: 30.0))->toBeFalse();
});

it('counts a point on an edge or a corner as inside', function () {
    $area = collectionOf(squareFeature(29.0, -3.0, 31.0, -1.0));

    expect(GeoJson::contains($area, lat: -2.0, lon: 29.0))->toBeTrue()
        ->and(GeoJson::contains($area, lat: -1.0, lon: 30.0))->toBeTrue()
        ->and(GeoJson::contains($area, lat: -3.0, lon: 31.0))->toBeTrue();
});

it('treats the inside of a hole as outside', function () {
    $outer = [[0, 0], [10, 0], [10, 10], [0, 10], [0, 0]];
    $hole = [[4, 4], [6, 4], [6, 6], [4, 6], [4, 4]];
    $area = ['type' => 'Feature', 'geometry' => ['type' => 'Polygon', 'coordinates' => [$outer, $hole]]];

    expect(GeoJson::contains($area, lat: 5, lon: 5))->toBeFalse()
        ->and(GeoJson::contains($area, lat: 2, lon: 2))->toBeTrue()
        ->and(GeoJson::contains($area, lat: 5, lon: 4))->toBeTrue(); // on the hole's edge
});

it('handles several polygons in a MultiPolygon and in a collection', function () {
    $multi = ['type' => 'Feature', 'geometry' => ['type' => 'MultiPolygon', 'coordinates' => [
        [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]],
        [[[5, 5], [6, 5], [6, 6], [5, 6], [5, 5]]],
    ]]];

    expect(GeoJson::contains($multi, lat: 0.5, lon: 0.5))->toBeTrue()
        ->and(GeoJson::contains($multi, lat: 5.5, lon: 5.5))->toBeTrue()
        ->and(GeoJson::contains($multi, lat: 3, lon: 3))->toBeFalse();

    $two = collectionOf(squareFeature(0, 0, 1, 1), squareFeature(5, 5, 6, 6));
    expect(GeoJson::contains($two, lat: 5.5, lon: 5.5))->toBeTrue();
});

it('computes the bounding box over every polygon', function () {
    $area = collectionOf(squareFeature(29.0, -3.0, 31.0, -1.0), squareFeature(34.0, 0.0, 35.0, 2.0));

    expect(GeoJson::bbox($area))->toBe(['min_lon' => 29.0, 'min_lat' => -3.0, 'max_lon' => 35.0, 'max_lat' => 2.0]);
});

it('returns no bounding box and no match for GeoJSON without polygons', function () {
    $empty = ['type' => 'FeatureCollection', 'features' => []];
    $point = ['type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [30, -2]]];

    expect(GeoJson::bbox($empty))->toBeNull()
        ->and(GeoJson::contains($empty, lat: 0, lon: 0))->toBeFalse()
        ->and(GeoJson::contains($point, lat: -2, lon: 30))->toBeFalse();
});
