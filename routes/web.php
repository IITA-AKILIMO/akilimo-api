<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\CassavaUnitController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CropController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DefaultPriceController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\FertilizerController;
use App\Http\Controllers\Admin\FertilizerTypeController;
use App\Http\Controllers\Admin\PriceListController;
use App\Http\Controllers\Admin\ReferenceDataController;
use App\Http\Controllers\Admin\RequestLogController;
use App\Http\Controllers\Admin\StarchFactoryController;
use App\Http\Controllers\Admin\StarchPriceController;
use App\Http\Controllers\Admin\TrafficExclusionController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Web\HealthCheckController;
use App\Http\Controllers\Web\PlaygroundAuthController;
use App\Http\Controllers\Web\PlaygroundController;
use App\Http\Controllers\Web\PlaygroundTokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'check']);
});

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

// ── Admin — public ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.post');
});

// ── Admin — authenticated (admin + partner) ────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth:web', 'role:' . EnumUserRole::adminMiddlewareParam()])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // API Keys (partners manage their own keys)
    Route::patch('api-keys/{id}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');
    Route::patch('api-keys/{id}/activate', [ApiKeyController::class, 'activate'])->name('api-keys.activate');
    Route::resource('api-keys', ApiKeyController::class)
        ->except(['show'])
        ->parameters(['api-keys' => 'id']);

    // Monitoring (partners can view their own request/feedback data)
    Route::get('requests', [RequestLogController::class, 'index'])->name('requests.index');
    Route::get('requests/{id}', [RequestLogController::class, 'show'])->name('requests.show');
    Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');

    // ── Admin-only routes ──────────────────────────────────────────────────────
    Route::middleware('role:' . EnumUserRole::Admin->value)->group(function () {
        // Make a request's PDF report again from its stored payload (calls the compute service)
        Route::post('requests/{id}/report', [RequestLogController::class, 'regenerateReport'])
            ->middleware('throttle:write')->name('requests.report');

        // Users
        Route::resource('users', UserController::class)
            ->except(['show'])
            ->parameters(['users' => 'user']);

        // Management
        Route::resource('fertilizers', FertilizerController::class)
            ->except(['show'])
            ->parameters(['fertilizers' => 'id']);

        // Reference data and price settings: the lists the price forms choose from and the rules the app's lists use
        Route::get('reference', [ReferenceDataController::class, 'hub'])->name('reference.hub');
        Route::get('reference/{slug}', [ReferenceDataController::class, 'index'])->name('reference.index');
        Route::get('reference/{slug}/create', [ReferenceDataController::class, 'create'])->name('reference.create');
        Route::post('reference/{slug}', [ReferenceDataController::class, 'store'])->name('reference.store');
        Route::get('reference/{slug}/edit', [ReferenceDataController::class, 'edit'])->name('reference.edit');
        Route::put('reference/{slug}', [ReferenceDataController::class, 'update'])->name('reference.update');
        Route::delete('reference/{slug}', [ReferenceDataController::class, 'destroy'])->name('reference.destroy');

        // What the app's price lists contain right now (read-only)
        Route::get('price-lists', [PriceListController::class, 'index'])->name('price-lists.index');

        // A new fertilizer (its type code is generated from the name)
        Route::get('fertilizer-types/create', [FertilizerTypeController::class, 'create'])->name('fertilizer-types.create');
        Route::post('fertilizer-types', [FertilizerTypeController::class, 'store'])->name('fertilizer-types.store');

        // Starch prices — batch routes must come before the resource to avoid {id} capture
        Route::get('starch-prices/batch-create', [StarchPriceController::class, 'batchCreate'])->name('starch-prices.batch-create');
        Route::post('starch-prices/batch', [StarchPriceController::class, 'batchStore'])->name('starch-prices.batch-store');
        Route::get('starch-prices/batch-edit', [StarchPriceController::class, 'batchEdit'])->name('starch-prices.batch-edit');
        Route::put('starch-prices/batch', [StarchPriceController::class, 'batchUpdate'])->name('starch-prices.batch-update');

        Route::resource('starch-prices', StarchPriceController::class)
            ->except(['show'])
            ->parameters(['starch-prices' => 'id']);

        // Default prices — batch and workflow routes before resource
        Route::get('default-prices/batch-create', [DefaultPriceController::class, 'batchCreate'])->name('default-prices.batch-create');
        Route::post('default-prices/batch', [DefaultPriceController::class, 'batchStore'])->name('default-prices.batch-store');
        Route::get('default-prices/batch-edit', [DefaultPriceController::class, 'batchEdit'])->name('default-prices.batch-edit');
        Route::put('default-prices/batch', [DefaultPriceController::class, 'batchUpdate'])->name('default-prices.batch-update');

        Route::post('default-prices/{id}/activate', [DefaultPriceController::class, 'activate'])->name('default-prices.activate');
        Route::post('default-prices/{id}/confirm', [DefaultPriceController::class, 'confirm'])->name('default-prices.confirm');

        Route::resource('default-prices', DefaultPriceController::class)
            ->except(['show'])
            ->parameters(['default-prices' => 'id']);

        // Supporting data
        Route::resource('starch-factories', StarchFactoryController::class)
            ->except(['show'])
            ->parameters(['starch-factories' => 'id']);

        Route::resource('crops', CropController::class)
            ->except(['show'])
            ->parameters(['crops' => 'id']);

        Route::resource('currencies', CurrencyController::class)
            ->except(['show'])
            ->parameters(['currencies' => 'id']);

        Route::resource('countries', CountryController::class)
            ->except(['show'])
            ->parameters(['countries' => 'id']);

        Route::resource('cassava-units', CassavaUnitController::class)
            ->except(['show'])
            ->parameters(['cassava-units' => 'id']);

        // Translations — batch edit (create + edit unified in one spreadsheet page)
        Route::get('translations/batch-edit', [TranslationController::class, 'batchEdit'])->name('translations.batch-edit');
        Route::put('translations/batch', [TranslationController::class, 'batchUpdate'])->name('translations.batch-update');

        Route::resource('translations', TranslationController::class)
            ->except(['show'])
            ->parameters(['translations' => 'id']);

        // Rules marking staff and test traffic, left out of the dashboard and reports
        Route::resource('traffic-exclusions', TrafficExclusionController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['traffic-exclusions' => 'id']);
    });
});
