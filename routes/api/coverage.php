<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\CoverageController;
use Illuminate\Support\Facades\Route;

// ── Supported area (where recommendations can be computed) ────────────────────
Route::middleware('throttle:read')->prefix('v1/coverage')->group(function () {
    Route::get('/', [CoverageController::class, 'index']);
    Route::get('/geojson', [CoverageController::class, 'all']);
    Route::get('/check', [CoverageController::class, 'check']);
    Route::get('/{country}', [CoverageController::class, 'show']);
});
