<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoverageArea;
use App\Repositories\CoverageAreaRepo;
use App\Service\CoverageService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CoverageController extends Controller
{
    public function __construct(
        protected CoverageAreaRepo $repo,
        protected CoverageService $coverage,
    ) {}

    /**
     * List Coverage
     *
     * The countries that have a supported area, with a bounding box ([min lon, min lat, max lon, max lat])
     * and when it was last copied from the compute service. The geometry itself is under
     * `/v1/coverage/{country}` and `/v1/coverage/geojson`.
     *
     * @unauthenticated
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->repo->summaries()->map(fn (CoverageArea $area) => [
                'country_code' => $area->country_code,
                'n_cells' => $area->n_cells,
                'bbox' => [$area->min_lon, $area->min_lat, $area->max_lon, $area->max_lat],
                'fetched_at' => $area->fetched_at,
            ])->values(),
        ]);
    }

    /**
     * Supported Area (all countries)
     *
     * One GeoJSON FeatureCollection with a feature per country, for drawing the supported areas on a map.
     *
     * @unauthenticated
     */
    public function all(): Response
    {
        $features = $this->repo->allWithGeometry()->flatMap(function (CoverageArea $area) {
            $collection = json_decode($area->geojson, true);

            return $collection['features'] ?? [];
        })->values()->all();

        return response(json_encode(['type' => 'FeatureCollection', 'features' => $features], JSON_UNESCAPED_SLASHES), 200, [
            'Content-Type' => 'application/geo+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Supported Area (one country)
     *
     * The GeoJSON of the area where AKILIMO can produce a recommendation for the country. Use it to draw the
     * area on a map or to check a location before asking for a recommendation.
     *
     * @unauthenticated
     */
    public function show(string $country): Response|JsonResponse
    {
        $area = $this->repo->findByCountry($country);

        if ($area === null) {
            return response()->json(['message' => 'No supported area is available for this country.'], 404);
        }

        return response($area->geojson, 200, [
            'Content-Type' => 'application/geo+json',
            'Cache-Control' => 'public, max-age=3600',
            'ETag' => '"' . $area->checksum . '"',
        ]);
    }

    /**
     * Check a Location
     *
     * Is a point inside the supported area of a country? `covered` is `true` or `false`, or `null` when no
     * supported area has been loaded for the country (unknown: do not treat it as outside).
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'country', description: 'ISO 3166-1 alpha-2 country code.', type: 'string')]
    #[QueryParameter(name: 'lat', description: 'Latitude in decimal degrees.', type: 'number')]
    #[QueryParameter(name: 'lon', description: 'Longitude in decimal degrees.', type: 'number')]
    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $country = strtoupper($data['country']);
        $covered = $this->coverage->contains($country, (float)$data['lat'], (float)$data['lon']);

        return response()->json(['data' => [
            'country_code' => $country,
            'lat' => (float)$data['lat'],
            'lon' => (float)$data['lon'],
            'covered' => $covered,
            'message' => match ($covered) {
                true => 'This location is inside the supported area.',
                false => "This location is outside the area AKILIMO supports in {$country}.",
                null => "No supported area has been loaded for {$country} yet, so this location cannot be checked.",
            },
        ]]);
    }
}
