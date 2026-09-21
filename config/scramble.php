<?php

use App\Support\Documentation\GoToDefinitionOperationExtension;
use App\Support\Documentation\GoToDefinitionSchemaExtension;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

return [
    /*
     * Which routes to document. String or array form; use Scramble::routes() for custom selection.
     *
     * 'api_path' => [
     *     'include' => 'api',
     *     'exclude' => ['api/internal'],
     * ],
     *
     * Without *, patterns match path segments (api matches api and api/users, not apiary).
     * With *, Str::is is used (e.g. api/v*).
     *
     * One static include → default server is /{include} and paths are stripped (/users).
     * Multiple includes or wildcards → server defaults to / and paths stay full (/api/users).
     * Override with `servers`, or use Scramble::registerApi() for separate bases.
     */
    'api_path' => 'api',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '1.0.0'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => file_get_contents(__DIR__.'/../README.md'),
    ],

    'ui' => [
        'title' => 'AKILIMO API',
        /*
         * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
         */
        'try_it_credentials_policy' => 'include',
    ],

    'renderer' => 'scalar',

    'renderers' => [
        /*
         * Stoplight Elements config options: https://docs.stoplight.io/docs/elements/b074dc47b2826-elements-configuration-options
         */
        'elements' => [
            'view' => 'scramble::docs',
            'theme' => 'dark',
            'hideTryIt' => false,
            'hideSchemas' => true,
            'logo' => '/images/akilimo_logo.svg',
            'tryItCredentialsPolicy' => 'include',
            'layout' => 'responsive',
            'router' => 'hash',
        ],
        /*
         * Scalar API reference config options: https://scalar.com/products/api-references/configuration
         */
        'scalar' => [
            /*
             * View and Core Settings
             */
            'view' => 'scramble::scalar', // Blade template used to render Scalar
            'cdn' => 'https://cdn.jsdelivr.net/npm/@scalar/api-reference', // Custom CDN bundle source
            'proxyUrl' => 'https://proxy.scalar.com', // CORS proxy URL for Try It feature
            'credentials' => 'include', // Fetch credentials policy: 'omit', 'include', 'same-origin'

            /*
             * Theme & Styling
             * Themes available: 'default', 'alternate', 'moon', 'purple', 'solarized',
             *                   'bluePlanet', 'saturn', 'kepler', 'mars', 'deepSpace', 'laserwave', 'none'
             */
            'theme' => 'purple', // Preset color theme
            'darkMode' => true, // Enables dark mode by default
            /*
             * Layout & Interface Elements
             */
            'layout' => 'modern', // Interface layout style: 'modern' or 'classic'
            'showSidebar' => true, // Show or hide the navigation sidebar
            'hideModels' => true, // Hide the Models/Schemas section from sidebar and page bottom
            'hideSearch' => false, // Hide the global search input bar
            'searchHotKey' => 'k', // Keyboard shortcut key for search bar (e.g., 'k', 'f')
            'showDeveloperTools' => 'never', // Options: 'never', 'always'

            /*
             * Operation & Schema Ordering
             */
            'tagSorter' => 'alpha', // Tag list order: 'alpha' or 'order' (OpenAPI order)
            'operationSorter' => 'method', // Endpoint order: 'alpha' or 'method'
            'schemaPropertyOrder' => 'alpha', // Schema property order: 'alpha' or 'preserve'

            /*
             * Interactive Client & Download Buttons
             */
            'hideTestRequestButton' => false, // Hide the "Test Request" / "Try It Out" button
            'documentDownloadType' => 'both', // OpenAPI spec download options: 'json', 'yaml', 'both', 'none'
            'defaultHttpClient' => [ // Default code snippet language/client selected
                'targetKey' => 'shell',
                'clientKey' => 'curl',
            ],
            'agent' => [
                'disabled' => true, // Disables Scalar background API agent / telemetry
            ],
        ],
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
//    'servers' => null,
    'servers' => [
        'Local'      => 'api',
        'Production' => 'https://api.akilimo.org',
        'Staging'    => 'https://staging-api.akilimo.org',
    ],

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     * @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    /**
     * Determines how Scramble stores the names of enum cases.
     * Available options:
     * - 'names' – Case names are stored in the `x-enumNames` enum schema extension.
     * - 'varnames' - Case names are stored in the `x-enum-varnames` enum schema extension.
     * - false - Case names are not stored.
     */
    'enum_cases_names_strategy' => false,

    /**
     * When Scramble encounters deep objects in query parameters, it flattens the parameters so the generated
     * OpenAPI document correctly describes the API. Flattening deep query parameters is relevant until
     * OpenAPI 3.2 is released and query string structure can be described properly.
     *
     * For example, this nested validation rule describes the object with `bar` property:
     * `['foo.bar' => ['required', 'int']]`.
     *
     * When `flatten_deep_query_parameters` is `true`, Scramble will document the parameter like so:
     * `{"name":"foo[bar]", "schema":{"type":"int"}, "required":true}`.
     *
     * When `flatten_deep_query_parameters` is `false`, Scramble will document the parameter like so:
     *  `{"name":"foo", "schema": {"type":"object", "properties":{"bar":{"type": "int"}}, "required": ["bar"]}, "required":true}`.
     */
    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [
        GoToDefinitionOperationExtension::class,
        GoToDefinitionSchemaExtension::class,
    ],

    /*
     * Automatically document API security (OpenAPI `security` / `securitySchemes`) based on route
     * middleware.
     *
     * Disabled by default. Uncomment the line below to enable `MiddlewareAuthSecurityStrategy`.
     * When at least one documented route uses middleware matching the configured patterns (by default
     * `auth` and `auth:*`), bearer auth is applied globally. Routes without matching middleware are
     * marked as public (`security: []`).
     *
     * Set to `null` explicitly to disable. If you already configure security manually via
     * `afterOpenApiGenerated` / `extendOpenApi`, keep this disabled to avoid duplicate schemes.
     *
     * Customize with a class-string or [class, options]:
     *
     * 'security_strategy' => [
     *     \Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy::class,
     *     [
     *         'middleware' => ['auth', 'auth:*'],
     *         'scheme' => \Dedoc\Scramble\Support\Generator\SecurityScheme::http('bearer'),
     *     ],
     * ],
     */
    'security_strategy' => null,
//    'security_strategy' => MiddlewareAuthSecurityStrategy::class,
//    'security_strategy' => [
//        MiddlewareAuthSecurityStrategy::class,
//        [
//            'middleware' => ['auth', 'auth:*'],
//            'scheme' => SecurityScheme::http('bearer'),
//        ],
//    ],
];
