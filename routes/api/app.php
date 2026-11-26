<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\AppVersionController;
use Illuminate\Support\Facades\Route;

// ── Mobile app release gate (minimum and latest version code) ─────────────────
Route::middleware('throttle:read')->get('v1/app/version', [AppVersionController::class, 'index']);
