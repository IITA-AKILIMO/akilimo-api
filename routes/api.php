<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CassavaPricesController;
use App\Http\Controllers\Api\CassavaUnitsController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CoverageController;
use App\Http\Controllers\Api\CropController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\FertilizerController;
use App\Http\Controllers\Api\FertilizerPriceController;
use App\Http\Controllers\Api\InvestmentAmountController;
use App\Http\Controllers\Api\MaizePricesController;
use App\Http\Controllers\Api\MaizeUnitsController;
use App\Http\Controllers\Api\McpKeyController;
use App\Http\Controllers\Api\OperationCostController;
use App\Http\Controllers\Api\PotatoPricesController;
use App\Http\Controllers\Api\PriceCatalogueController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\StarchFactoryController;
use App\Http\Controllers\Api\StarchPricesController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserFeedbackController;
use App\Http\Controllers\Web\HealthCheckController;
use Illuminate\Support\Facades\Route;

// ── Health ────────────────────────────────────────────────────────────────────
Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'check']);
});

// ── Authentication ────────────────────────────────────────────────────────────
Route::middleware('throttle:auth')->prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.token');
});

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
});

Route::middleware(['throttle:write', 'auth.token', 'farmer'])->prefix('v1/account')->group(function () {
    Route::post('/consent', [AccountController::class, 'consent']);
    Route::post('/logout', [AccountController::class, 'logout']);
});

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

// ── Supported area (where recommendations can be computed) ────────────────────
Route::middleware('throttle:read')->prefix('v1/coverage')->group(function () {
    Route::get('/', [CoverageController::class, 'index']);
    Route::get('/geojson', [CoverageController::class, 'all']);
    Route::get('/check', [CoverageController::class, 'check']);
    Route::get('/{country}', [CoverageController::class, 'show']);
});

// ── Recommendations ───────────────────────────────────────────────────────────
// Route::middleware(['throttle:compute', 'auth.token:recommendations:compute'])->group(function () {
//    Route::post('v1/recommendations/compute', [RecommendationController::class, 'computeRecommendations']);
// });

Route::middleware(['throttle:compute'])->group(function () {
    Route::post('v1/recommendations/compute', [RecommendationController::class, 'computeRecommendations']);
});

// PDF report of a recommendation: only through the signed, expiring link in the compute answer
Route::middleware(['throttle:read', 'signed:relative'])
    ->get('v1/recommendations/reports/{report}/{file}', [RecommendationController::class, 'report'])
    ->where('file', '[A-Za-z0-9_.-]+\.pdf')
    ->name('recommendations.report');

// New links for a stored report, e.g. for saved history whose link has expired (the MCP server's own key)
Route::middleware(['throttle:read', 'auth.token:reports:read'])
    ->get('v1/recommendations/reports/{report}', [RecommendationController::class, 'reportLinks'])
    ->whereUuid('report');

Route::middleware(['throttle:read', 'auth.token:recommendations:read'])->group(function () {
    Route::get('v1/recommendations', [RecommendationController::class, 'index']);
});

// ── User feedback ─────────────────────────────────────────────────────────────
Route::middleware(['throttle:write', 'auth.token:feedback:write'])->group(function () {
    Route::post('v1/user-feedback', [UserFeedbackController::class, 'store']);
});

Route::middleware(['throttle:read', 'auth.token:feedback:read'])->group(function () {
    Route::get('v1/user-feedback', [UserFeedbackController::class, 'index']);
});

// ── Translations ──────────────────────────────────────────────────────────────
Route::middleware(['throttle:read', 'auth.token:translations:read'])->group(function () {
    Route::get('v1/translations', [TranslationController::class, 'index']);
});

// ── Partner — price submissions (requires prices:write) ───────────────────────
Route::middleware(['throttle:write', 'auth.token:prices:write'])->prefix('v1/partner')->group(function () {});

// ── API key self-management ───────────────────────────────────────────────────
Route::middleware(['throttle:write', 'auth.token:api-keys:manage'])->prefix('v1/auth')->group(function () {
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::patch('/api-keys/{id}/revoke', [ApiKeyController::class, 'revoke']);
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy']);
});

// ── MCP key check (the AKILIMO MCP server's own key, requires mcp:verify-keys) ──
Route::middleware(['throttle:mcp-verify', 'auth.token:mcp:verify-keys'])->prefix('v1/mcp')->group(function () {
    Route::post('/verify-key', [McpKeyController::class, 'verify']);
});

// ── Admin — lookup data CRUD (requires write)─────────────────────────────────
Route::middleware(['throttle:admin-write', 'auth.token:write'])->prefix('v1/admin')->group(function () {
    Route::apiResource('fertilizers', FertilizerController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('starch-prices', StarchPricesController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('starch-factories', StarchFactoryController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('currencies', CurrencyController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('cassava-units', CassavaUnitsController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('translations', TranslationController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('price-catalogue', PriceCatalogueController::class)->only(['store', 'update', 'destroy']);
});

// ── Admin — user management (requires admin) ──────────────────────────────────
Route::middleware(['throttle:write', 'auth.token:admin'])->prefix('v1/admin')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});
