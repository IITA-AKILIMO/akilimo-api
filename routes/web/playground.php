<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Web\PlaygroundAuthController;
use App\Http\Controllers\Web\PlaygroundController;
use App\Http\Controllers\Web\PlaygroundTokenController;
use Illuminate\Support\Facades\Route;

// ── Playground — public auth ───────────────────────────────────────────────────
Route::prefix('playground')->name('playground.')->group(function () {
    Route::get('/login', [PlaygroundAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PlaygroundAuthController::class, 'login'])->name('login.post')->middleware('throttle:auth');
    Route::get('/register', [PlaygroundAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [PlaygroundAuthController::class, 'register'])->name('register.post')->middleware('throttle:sensitive');
    Route::get('/check-username', [PlaygroundAuthController::class, 'checkUsername'])->name('check-username')->middleware('throttle:read');
    Route::post('/logout', [PlaygroundAuthController::class, 'logout'])->name('logout');

    // Public: it only displays the public /health checks, and the welcome page links here
    Route::get('/health', [PlaygroundController::class, 'showHealth'])->name('health');
});

// ── Playground — authenticated ─────────────────────────────────────────────────
Route::middleware(['auth:web'])->group(function () {
    Route::get('/playground', [PlaygroundController::class, 'show']);
    Route::get('/playground/history', [PlaygroundController::class, 'history']);
    Route::middleware('throttle:sensitive')->post('/playground/compute', [PlaygroundController::class, 'compute']);

    // Token management
    Route::get('/playground/tokens', [PlaygroundTokenController::class, 'index']);
    Route::post('/playground/tokens', [PlaygroundTokenController::class, 'store'])->middleware('throttle:auth');
    Route::post('/playground/tokens/{id}/revoke', [PlaygroundTokenController::class, 'revoke']);
    Route::delete('/playground/tokens/{id}', [PlaygroundTokenController::class, 'destroy']);
});
