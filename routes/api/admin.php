<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\CassavaUnitsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\FertilizerController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PriceCatalogueController;
use App\Http\Controllers\Api\StarchFactoryController;
use App\Http\Controllers\Api\StarchPricesController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ── Partner — price submissions (requires prices:write) ───────────────────────
Route::middleware(['throttle:write', 'auth.token:prices:write'])->prefix('v1/partner')->group(function () {});

// ── Admin — lookup data CRUD (requires write)─────────────────────────────────
Route::middleware(['throttle:admin-write', 'auth.token:write'])->prefix('v1/admin')->group(function () {
    Route::apiResource('fertilizers', FertilizerController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('starch-prices', StarchPricesController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('starch-factories', StarchFactoryController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('currencies', CurrencyController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('cassava-units', CassavaUnitsController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('translations', TranslationController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('price-catalogue', PriceCatalogueController::class)->only(['store', 'update', 'destroy']);
    Route::get('messages', [MessageController::class, 'adminIndex']);
    Route::post('messages', [MessageController::class, 'store']);
});

// ── Admin — user management (requires admin) ──────────────────────────────────
Route::middleware(['throttle:write', 'auth.token:admin'])->prefix('v1/admin')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});
