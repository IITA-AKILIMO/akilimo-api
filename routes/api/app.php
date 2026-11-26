<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\AppVersionController;
use App\Http\Controllers\Api\DeviceController;
use Illuminate\Support\Facades\Route;

// ── Mobile app release gate (minimum and latest version code) ─────────────────
Route::middleware('throttle:read')->get('v1/app/version', [AppVersionController::class, 'index']);

// ── Phones that can be pushed to one by one (open; a farmer token, if sent, links the phone to the account) ──
Route::middleware(['throttle:write', 'farmer.optional'])->prefix('v1/devices')->group(function () {
    Route::put('/', [DeviceController::class, 'upsert']);
    Route::delete('/', [DeviceController::class, 'destroy']);
});
