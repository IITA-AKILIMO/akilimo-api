<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support;

/**
 * Just enough GeoJSON for the coverage areas: bounding boxes and point-in-polygon on Polygon and
 * MultiPolygon geometries (holes included). Coordinates are [lon, lat] as in GeoJSON.
 */
class GeoJson
{
    /**
     * Every polygon of a FeatureCollection, Feature or geometry; each polygon is a list of rings
     * (the first is the outer ring, the others are holes) of [lon, lat] points.
     *
     * @return list<list<list<array{0: float, 1: float}>>>
     */
    public static function polygons(array $geojson): array
    {
        $type = $geojson['type'] ?? null;

        if ($type === 'FeatureCollection') {
            return array_merge([], ...array_map(fn ($feature) => self::polygons($feature), $geojson['features'] ?? []));
        }
        if ($type === 'Feature') {
            return isset($geojson['geometry']) ? self::polygons($geojson['geometry']) : [];
        }
        if ($type === 'Polygon') {
            return [$geojson['coordinates'] ?? []];
        }
        if ($type === 'MultiPolygon') {
            return $geojson['coordinates'] ?? [];
        }
        if ($type === 'GeometryCollection') {
            return array_merge([], ...array_map(fn ($geometry) => self::polygons($geometry), $geojson['geometries'] ?? []));
        }

        return [];
    }

    /**
     * @return array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}|null null when there is no polygon
     */
    public static function bbox(array $geojson): ?array
    {
        $box = null;
        foreach (self::polygons($geojson) as $polygon) {
            foreach ($polygon[0] ?? [] as [$lon, $lat]) {
                $box ??= ['min_lon' => $lon, 'min_lat' => $lat, 'max_lon' => $lon, 'max_lat' => $lat];
                $box['min_lon'] = min($box['min_lon'], $lon);
                $box['min_lat'] = min($box['min_lat'], $lat);
                $box['max_lon'] = max($box['max_lon'], $lon);
                $box['max_lat'] = max($box['max_lat'], $lat);
            }
        }

        return $box === null ? null : array_map('floatval', $box);
    }

    /**
     * Is the point inside any polygon? A point exactly on an edge counts as inside.
     */
    public static function contains(array $geojson, float $lat, float $lon): bool
    {
        foreach (self::polygons($geojson) as $polygon) {
            if (self::polygonContains($polygon, $lon, $lat)) {
                return true;
            }
        }

        return false;
    }

    private static function polygonContains(array $rings, float $x, float $y): bool
    {
        if ($rings === []) {
            return false;
        }

        $outer = self::ringPosition($x, $y, $rings[0]);
        if ($outer === 0) {
            return false;
        }
        if ($outer === -1) {
            return true;
        }

        foreach (array_slice($rings, 1) as $hole) {
            $position = self::ringPosition($x, $y, $hole);
            if ($position === 1) {
                return false; // inside a hole
            }
        }

        return true;
    }

    /**
     * 1 inside the ring, 0 outside, -1 on its edge (ray casting).
     */
    private static function ringPosition(float $x, float $y, array $ring): int
    {
        $epsilon = 1e-12;
        $inside = false;
        $count = count($ring);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];

            $cross = ($x - $xi) * ($yj - $yi) - ($y - $yi) * ($xj - $xi);
            if (abs($cross) < $epsilon
                && $x >= min($xi, $xj) - $epsilon && $x <= max($xi, $xj) + $epsilon
                && $y >= min($yi, $yj) - $epsilon && $y <= max($yi, $yj) + $epsilon) {
                return -1;
            }

            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside ? 1 : 0;
    }
}
