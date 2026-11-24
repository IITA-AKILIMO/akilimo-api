<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Web\Playground\AuthController;
use App\Http\Controllers\Web\Playground\PlaygroundController;
use App\Http\Controllers\Web\Playground\TokenController;
use Illuminate\Support\Facades\Route;

// The `playground` middleware answers 404 on all of these while PLAYGROUND_ENABLED is false (config/playground.php)
Route::middleware('playground')->group(function () {
    // ── Public auth ────────────────────────────────────────────────────────────
    Route::prefix('playground')->name('playground.')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:auth');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:sensitive');
        Route::get('/check-username', [AuthController::class, 'checkUsername'])->name('check-username')->middleware('throttle:read');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Public: it only displays the public /health checks, and the welcome page links here
        Route::get('/health', [PlaygroundController::class, 'showHealth'])->name('health');
    });

    // ── Authenticated ──────────────────────────────────────────────────────────
    Route::middleware(['auth:web'])->group(function () {
        Route::get('/playground', [PlaygroundController::class, 'show']);
        Route::get('/playground/history', [PlaygroundController::class, 'history']);
        Route::middleware('throttle:sensitive')->post('/playground/compute', [PlaygroundController::class, 'compute']);

        // Token management
        Route::get('/playground/tokens', [TokenController::class, 'index']);
        Route::post('/playground/tokens', [TokenController::class, 'store'])->middleware('throttle:auth');
        Route::post('/playground/tokens/{id}/revoke', [TokenController::class, 'revoke']);
        Route::delete('/playground/tokens/{id}', [TokenController::class, 'destroy']);
    });
});
