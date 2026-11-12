<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Any S3-compatible store (AWS, or a self-hosted one such as Alarik): set AWS_ENDPOINT for the latter.
        // The defaults are the safe ones for the PDF reports, which hold personal data:
        //  - visibility private: every object is written private (no public-read ACL). If a store rejects ACL
        //    headers, set AWS_VISIBILITY= (empty) and keep the bucket itself private instead.
        //  - throw true: a failed write, read or delete raises an error instead of quietly returning false, so a
        //    broken bucket shows up in the log and in `php artisan reports:check-storage`.
        //  - short timeouts and two retries, so an unreachable store fails fast instead of hanging a request.
        //  - path-style addressing (endpoint/bucket/key), which self-hosted stores need unless every bucket name
        //    resolves in DNS. Set AWS_USE_PATH_STYLE_ENDPOINT=false for AWS itself.
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => filter_var(env('AWS_USE_PATH_STYLE_ENDPOINT', true), FILTER_VALIDATE_BOOL),
            'visibility' => env('AWS_VISIBILITY', 'private'),
            'throw' => true,
            'report' => false,
            'retries' => 2,
            'http' => [
                'connect_timeout' => 5,
                'timeout' => 30,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
