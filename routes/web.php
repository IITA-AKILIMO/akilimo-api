<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Web\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'check']);
});

// Split by area, one file each in routes/web/
require __DIR__ . '/web/account-deletion.php'; // public page to request account deletion
require __DIR__ . '/web/playground.php'; // public and signed-in playground
require __DIR__ . '/web/admin.php';      // admin UI (its pricing and lookup-data routes are in routes/web/admin/)
