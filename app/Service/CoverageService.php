<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Exceptions\RecommendationException;
use App\Models\CoverageArea;
use App\Repositories\CoverageAreaRepo;
use App\Support\GeoJson;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

/**
 * Keeps a copy of the compute service's supported area per country and answers "is this point
 * inside it?" The copy is refreshed by `coverage:sync`; until a country has been synced nothing is
 * known about it, and callers must not turn that into a rejection.
 */
class CoverageService
{
    /** Parsed GeoJSON per checksum, so a request does not re-decode a large polygon */
    private static array $parsed = [];

    public function __construct(
        protected CoverageAreaRepo $repo,
        protected AkilimoComputeService $compute,
    ) {}

    /**
     * Fetches coverage for the given countries (default: all configured) and stores what changed.
     *
     * @param list<string>|null $countries
     * @return array<string, array{status: string, message?: string}> status is created, updated, unchanged or failed
     */
    public function sync(?array $countries = null): array
    {
        $results = [];

        foreach ($countries ?: config('akilimo-compute.coverage.countries', []) as $country) {
            $country = strtoupper($country);

            try {
                $results[$country] = ['status' => $this->syncCountry($country)];
            } catch (Throwable $e) {
                report($e);
                $results[$country] = ['status' => 'failed', 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     * @throws RecommendationException
     * @throws \JsonException
     */
    private function syncCountry(string $country): string
    {
        $geoJson = $this->compute->coverage($country);

        $bbox = GeoJson::bbox($geoJson);
        if ($bbox === null) {
            throw new \RuntimeException("The coverage of {$country} has no polygon");
        }

        $json = json_encode($geoJson, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $checksum = hash('sha256', $json);
        $existing = $this->repo->findByCountry($country);

        if ($existing?->checksum === $checksum) {
            $existing->forceFill(['fetched_at' => now()])->save();

            return 'unchanged';
        }

        $this->repo->store($country, [
            'geojson' => $json,
            'n_cells' => $geoJson['features'][0]['properties']['n_cells'] ?? null,
            'checksum' => $checksum,
            'fetched_at' => now(),
            ...$bbox,
        ]);

        return $existing === null ? 'created' : 'updated';
    }

    /**
     * True if the point is inside the supported area of the country, false if it is outside, and null
     * when no coverage has been synced for the country (unknown, not a reason to reject).
     *
     * @throws \JsonException
     */
    public function contains(string $country, float $lat, float $lon): ?bool
    {
        $area = $this->repo->findByCountry($country);
        if ($area === null) {
            return null;
        }

        if ($lon < $area->min_lon || $lon > $area->max_lon || $lat < $area->min_lat || $lat > $area->max_lat) {
            return false;
        }

        return GeoJson::contains($this->geometry($area), $lat, $lon);
    }

    /**
     * @throws \JsonException
     */
    private function geometry(CoverageArea $area): array
    {
        return self::$parsed[$area->checksum] ??= json_decode($area->geojson, true, 512, JSON_THROW_ON_ERROR);
    }
}
