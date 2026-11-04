<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

/*
|--------------------------------------------------------------------------
| Request limits
|--------------------------------------------------------------------------
|
| Every limit of the API lives here: requests per minute and client IP. Each key is registered as a named
| limiter (AppServiceProvider) and used by the routes as `throttle:<key>`; all routes that use the same key
| share one counter per IP. The defaults are for production; raise any of them in .env for local development,
| where the web UI of the MCP agent makes many lookups before it computes.
|
*/

return [

    /** Signing in and key creation: POST /v1/auth/login and /logout, the playground sign-in and its key creation */
    'auth' => (int) env('API_RATE_AUTH_PER_MINUTE', 10),

    /** Tight limit for sensitive actions: admin sign-in, playground sign-up and the playground's compute button */
    'sensitive' => (int) env('API_RATE_SENSITIVE_PER_MINUTE', 5),

    /** Reads: public reference data, the supported-area routes, the username check, recommendation history, feedback list, translations */
    'read' => (int) env('API_RATE_READ_PER_MINUTE', 120),

    /** Writes: user feedback, partner price updates, API key management and admin operations */
    'write' => (int) env('API_RATE_WRITE_PER_MINUTE', 30),

    /** POST /v1/recommendations/compute (the R service behind it is slow, so keep it modest in production) */
    'compute' => (int) env('API_RATE_COMPUTE_PER_MINUTE', 30),

    /** Admin data changes (/v1/admin, the ones that need the write ability) */
    'admin-write' => (int) env('API_RATE_ADMIN_WRITE_PER_MINUTE', 60),

    /** POST /v1/mcp/verify-key, called by the MCP server for every new key it sees */
    'mcp-verify' => (int) env('API_RATE_MCP_VERIFY_PER_MINUTE', 600),

];
