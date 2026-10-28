# Supported area (coverage)

AKILIMO can only compute a recommendation where the compute service has yield and soil data. The compute service (`akilimo-recommendations`) publishes that area as GeoJSON; this API keeps a copy, serves it to maps, and refuses compute requests that fall outside it before they reach the compute service.

## Flow

```
compute service  GET /coverage/{country}   (GeoJSON, one FeatureCollection per country)
        │   php artisan coverage:sync      (daily at 03:30, or by hand)
        ▼
coverage_areas table  (country_code, geojson, bbox, n_cells, checksum, fetched_at)
        │
        ├── GET /api/v1/coverage            list: countries, bbox, last fetched
        ├── GET /api/v1/coverage/geojson    every country in one FeatureCollection
        ├── GET /api/v1/coverage/{country}  one country as GeoJSON (cached, ETag)
        ├── GET /api/v1/coverage/check      ?country=NG&lat=7.4&lon=3.9 → covered true / false / null
        └── POST /api/v1/recommendations/compute   422 when the location is outside
```

All coverage routes are public (throttled like the other reference data).

## Ingest

`php artisan coverage:sync [NG TZ ...]` fetches `{AKILIMO_COMPUTE_BASE_URL}/coverage/{country}` for each country in `config('akilimo-compute.coverage.countries')` (NG, TZ, RW, GH, BI) and upserts one row per country. The result per country is `created`, `updated`, `unchanged` (same SHA-256, only `fetched_at` moves) or `failed` (the others are still stored; the command exits 1). The schedule is in `routes/console.php`; the host needs the usual `php artisan schedule:run` cron.

First deployment:

```bash
php artisan migrate
php artisan coverage:sync
```

If the routes are cached on the server (`bootstrap/cache/routes-v7.php`), run `php artisan route:cache` again so the new routes are seen.

## Early validation

`ComputeRequest::after()` checks `compute_request.farmInformation` against the stored area and answers 422 on `compute_request.farmInformation.map_lat`:

> This location is outside the area AKILIMO supports in NG. Check the coordinates against GET /v1/coverage/NG.

It never refuses anything when the country has no stored area (nothing synced yet, or the compute service was unreachable), so a missing or stale sync cannot block farmers. Set `COVERAGE_VALIDATE=false` to switch the check off.

The check is the same test the compute service applies (the area is the union of its 0.05° grid cells), with a point exactly on an edge counted as inside, so a rare edge case may pass here and still be refused by the compute service with its own 400.

## Notes

- Coordinates are WGS84, `[lon, lat]` as in GeoJSON; the check takes `lat` and `lon` separately.
- The area is the agricultural area with data, not the country: cities, lakes and some regions are outside on purpose.
- `App\Support\GeoJson` does the geometry (bounding box, point-in-polygon with holes); tests are in `tests/Unit/GeoJsonTest.php` and `tests/Feature/CoverageTest.php`.
