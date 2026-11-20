<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountHistoryController;
use Illuminate\Support\Facades\Route;

// ── Farmer accounts (optional; the app works without one) ────────────────────
Route::middleware('throttle:sensitive')->prefix('v1/account')->group(function () {
    Route::post('/register', [AccountController::class, 'register']);
    Route::post('/email/verify', [AccountController::class, 'verifyEmail']);
    Route::post('/password/forgot', [AccountController::class, 'forgotPassword']);
    Route::post('/password/reset', [AccountController::class, 'resetPassword']);
    Route::post('/email/resend', [AccountController::class, 'resendVerification'])->middleware(['auth.token', 'farmer']);
});

Route::middleware('throttle:auth')->prefix('v1/account')->group(function () {
    Route::post('/login', [AccountController::class, 'login']);
    Route::post('/google', [AccountController::class, 'google']);
    Route::post('/social/{provider}', [AccountController::class, 'social'])->where('provider', '[a-z0-9-]+');
});

Route::middleware(['throttle:write', 'auth.token', 'farmer'])->prefix('v1/account')->group(function () {
    Route::post('/consent', [AccountController::class, 'consent']);
    Route::post('/logout', [AccountController::class, 'logout']);
});

Route::middleware(['throttle:read', 'auth.token:history:own', 'farmer', 'farmer.consented'])->prefix('v1/account')->group(function () {
    Route::get('/history', [AccountHistoryController::class, 'index']);
    Route::get('/history/ids', [AccountHistoryController::class, 'ids']);
    Route::get('/history/{requestId}', [AccountHistoryController::class, 'show'])->whereUuid('requestId');
    Route::get('/history/{requestId}/report', [AccountHistoryController::class, 'report'])->whereUuid('requestId');
});

Route::middleware(['throttle:write', 'auth.token:history:own', 'farmer', 'farmer.consented'])->prefix('v1/account')->group(function () {
    Route::delete('/history/{requestId}', [AccountHistoryController::class, 'destroy'])->whereUuid('requestId');
    Route::post('/history/delete', [AccountHistoryController::class, 'destroyMany']);
});

Route::middleware(['throttle:sensitive', 'auth.token:history:own', 'farmer', 'farmer.consented'])->prefix('v1/account')->group(function () {
    Route::delete('/history', [AccountHistoryController::class, 'destroyAll']);
    Route::delete('/', [AccountHistoryController::class, 'destroyAccount']);
});
