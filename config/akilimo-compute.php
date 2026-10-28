<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Akilimo Compute Service Base URL
    |--------------------------------------------------------------------------
    | Root URL for the Akilimo compute service. In Docker you might use
    | http://akilimo:8000, otherwise set an external URL in .env.
    */
    'base_url' => env('AKILIMO_COMPUTE_BASE_URL', 'http://127.0.0.1:8000'),

    /*
    |--------------------------------------------------------------------------
    | Compute Endpoint
    |--------------------------------------------------------------------------
    | Path appended to the base URL for compute requests.
    */
    'endpoint' => env('AKILIMO_COMPUTE_ENDPOINT', '/compute'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    | Timeout in seconds for HTTP requests to Akilimo.
    */
    'timeout' => (int)env('AKILIMO_COMPUTE_TIMEOUT', 120),

    /*
    |--------------------------------------------------------------------------
    | Retry Attempts
    |--------------------------------------------------------------------------
    | Number of times to retry failed requests before giving up.
    */
    'retries' => (int)env('AKILIMO_COMPUTE_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Logging Toggle
    |--------------------------------------------------------------------------
    | Whether to log Akilimo requests and responses.
    */
    'logging' => filter_var(env('AKILIMO_COMPUTE_LOGGING', true), FILTER_VALIDATE_BOOL),

    /*
    |--------------------------------------------------------------------------
    | Supported Area (coverage)
    |--------------------------------------------------------------------------
    | The compute service publishes where it can produce a recommendation
    | (GET /coverage/{country}). `coverage:sync` copies that GeoJSON into the
    | coverage_areas table; the map draws it and compute requests are checked
    | against it. A country with nothing synced yet is never rejected.
    */
    'coverage' => [
        'countries' => ['NG', 'TZ', 'RW', 'GH', 'BI'],
        'validate_requests' => filter_var(env('COVERAGE_VALIDATE', true), FILTER_VALIDATE_BOOL),
    ],
];
