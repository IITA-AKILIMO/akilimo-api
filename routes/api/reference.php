<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\CassavaPricesController;
use App\Http\Controllers\Api\CassavaUnitsController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CropController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\FertilizerController;
use App\Http\Controllers\Api\FertilizerPriceController;
use App\Http\Controllers\Api\InvestmentAmountController;
use App\Http\Controllers\Api\MaizePricesController;
use App\Http\Controllers\Api\MaizeUnitsController;
use App\Http\Controllers\Api\OperationCostController;
use App\Http\Controllers\Api\PotatoPricesController;
use App\Http\Controllers\Api\PriceCatalogueController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\StarchFactoryController;
use App\Http\Controllers\Api\StarchPricesController;
use Illuminate\Support\Facades\Route;

// ── Public reference data ─────────────────────────────────────────────────────
Route::middleware('throttle:read')->group(function () {
    Route::prefix('v1/countries')->group(function () {
        Route::get('/', [CountryController::class, 'index']);
    });

    Route::prefix('v1/crops')->group(function () {
        Route::get('/', [CropController::class, 'index']);
        Route::get('/country/{countryCode}', [CropController::class, 'byCountry']);
    });

    Route::prefix('v1/currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index']);
    });

    Route::prefix('v1/fertilizers')->group(function () {
        Route::get('/', [FertilizerController::class, 'index']);
        Route::get('/country/{countryCode}', [FertilizerController::class, 'byCountry']);
    });

    Route::prefix('v1/fertilizer-prices')->group(function () {
        Route::get('/', [FertilizerPriceController::class, 'index']);
        Route::get('/{fertilizerKey}', [FertilizerPriceController::class, 'byFertilizerKey']);
        Route::get('/country/{countryCode}', [FertilizerPriceController::class, 'byCountry']);
    });

    Route::prefix('v1/investment-amounts')->group(function () {
        Route::get('/', [InvestmentAmountController::class, 'index']);
        Route::get('/country/{countryCode}', [InvestmentAmountController::class, 'byCountry']);
    });

    Route::prefix('v1/operation-costs')->group(function () {
        Route::get('/', [OperationCostController::class, 'index']);
        Route::get('/country/{countryCode}', [OperationCostController::class, 'byCountry']);
    });

    Route::prefix('v1/starch-factories')->group(function () {
        Route::get('/', [StarchFactoryController::class, 'index']);
        Route::get('/country/{countryCode}', [StarchFactoryController::class, 'byCountry']);
    });

    Route::prefix('v1/starch-prices')->group(function () {
        Route::get('/', [StarchPricesController::class, 'index']);
    });

    Route::prefix('v1/price-catalogue')->group(function () {
        Route::get('/', [PriceCatalogueController::class, 'index']);
    });

    Route::prefix('v1/prices')->group(function () {
        Route::get('/', [PriceController::class, 'index']);
    });

    Route::prefix('v1/cassava-units')->group(function () {
        Route::get('/', [CassavaUnitsController::class, 'index']);
    });

    Route::prefix('v1/maize-units')->group(function () {
        Route::get('/', [MaizeUnitsController::class, 'index']);
    });

    Route::prefix('v1/cassava-prices')->group(function () {
        Route::get('/', [CassavaPricesController::class, 'index']);
        Route::get('/country/{countryCode}', [CassavaPricesController::class, 'byCountry']);
    });

    Route::prefix('v1/potato-prices')->group(function () {
        Route::get('/', [PotatoPricesController::class, 'index']);
        Route::get('/country/{countryCode}', [PotatoPricesController::class, 'byCountry']);
    });

    Route::prefix('v1/maize-prices')->group(function () {
        Route::get('/', [MaizePricesController::class, 'index']);
        Route::get('/country/{countryCode}', [MaizePricesController::class, 'byCountry']);
    });
});
