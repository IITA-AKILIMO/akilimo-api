<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Web\HealthCheckController;
use Illuminate\Support\Facades\Route;

// Routes are split by area, one file each in routes/api/. Prefix and middleware come from bootstrap/app.php.

// ── Health ────────────────────────────────────────────────────────────────────
Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'check']);
});

require __DIR__ . '/api/auth.php';            // login, logout, API keys, MCP key check
require __DIR__ . '/api/account.php';         // optional farmer accounts and saved history
require __DIR__ . '/api/reference.php';       // public reference data
require __DIR__ . '/api/coverage.php';        // supported area
require __DIR__ . '/api/app.php';             // mobile app minimum and latest version code
require __DIR__ . '/api/recommendations.php'; // compute, reports, recommendation list
require __DIR__ . '/api/feedback.php';        // user feedback and translations
require __DIR__ . '/api/admin.php';           // lookup data CRUD, user management
