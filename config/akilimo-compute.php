<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Service\RecommendationReportService;

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

    /*
    |--------------------------------------------------------------------------
    | PDF reports
    |--------------------------------------------------------------------------
    | The compute service makes a PDF report for every recommendation and sends
    | it back when asked (returnPdf). The API keeps it on a private disk, next
    | to the recommendation history (reports/{request_id}/), and answers with a
    | signed link that expires after `link_ttl_days`; a new link can be made at
    | any time, so saved history keeps working. Reports are kept as long as the
    | history unless `keep_days` is set (then `reports:prune` deletes older
    | ones). A report holds the farmer's name, masked phone number and the
    | farm's location: keep the disk private and in the backups.
    */
    'reports' => [
        'enabled' => filter_var(env('AKILIMO_REPORTS_ENABLED', true), FILTER_VALIDATE_BOOL),
        // Any filesystem disk. AKILIMO_REPORTS_DISK if set, otherwise the app's default disk (FILESYSTEM_DISK), so
        // FILESYSTEM_DISK=s3 puts the reports on S3 too; `local` (storage/app/private) needs a persistent volume.
        // A `public` default is never used for reports: they hold personal data (see resolveDisk()).
        'disk' => RecommendationReportService::resolveDisk(env('AKILIMO_REPORTS_DISK'), env('FILESYSTEM_DISK')),
        'link_ttl_days' => (int)env('AKILIMO_REPORTS_LINK_TTL_DAYS', 7),
        // Empty or 0 = keep forever, like the recommendation history
        'keep_days' => (int)env('AKILIMO_REPORTS_KEEP_DAYS', 0),
        // Largest PDF kept (bytes); anything bigger is dropped and logged
        'max_bytes' => (int)env('AKILIMO_REPORTS_MAX_BYTES', 10 * 1024 * 1024),
    ],
];
