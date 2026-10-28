<?php

use App\Enums\EnumUserRole;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\CassavaPriceController;
use App\Http\Controllers\Admin\CassavaUnitController;
use App\Http\Controllers\Admin\CropController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DefaultPriceController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\FertilizerController;
use App\Http\Controllers\Admin\FertilizerPriceController;
use App\Http\Controllers\Admin\InvestmentAmountController;
use App\Http\Controllers\Admin\MaizePriceController;
use App\Http\Controllers\Admin\OperationCostController;
use App\Http\Controllers\Admin\PotatoPriceController;
use App\Http\Controllers\Admin\RequestLogController;
use App\Http\Controllers\Admin\StarchFactoryController;
use App\Http\Controllers\Admin\StarchPriceController;
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
    Route::post('/login', [PlaygroundAuthController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::get('/register', [PlaygroundAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [PlaygroundAuthController::class, 'register'])->name('register.post')->middleware('throttle:5,1');
    Route::get('/check-username', [PlaygroundAuthController::class, 'checkUsername'])->name('check-username')->middleware('throttle:60,1');
    Route::post('/logout', [PlaygroundAuthController::class, 'logout'])->name('logout');
});

// ── Playground — authenticated ─────────────────────────────────────────────────
Route::middleware(['auth:web'])->group(function () {
    Route::get('/playground', [PlaygroundController::class, 'show']);
    Route::get('/playground/history', [PlaygroundController::class, 'history']);
    Route::middleware('throttle:5,1')->post('/playground/compute', [PlaygroundController::class, 'compute']);

    // Token management
    Route::get('/playground/health', [PlaygroundController::class, 'showHealth']);
    Route::get('/playground/tokens', [PlaygroundTokenController::class, 'index']);
    Route::post('/playground/tokens', [PlaygroundTokenController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/playground/tokens/{id}/revoke', [PlaygroundTokenController::class, 'revoke']);
    Route::delete('/playground/tokens/{id}', [PlaygroundTokenController::class, 'destroy']);
});

// ── Admin — public ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.post');
});

// ── Admin — authenticated (admin + partner) ────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth:web', 'role:'.EnumUserRole::adminMiddlewareParam()])->group(function () {
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
    Route::middleware('role:'.EnumUserRole::Admin->value)->group(function () {
        // Users
        Route::resource('users', UserController::class)
            ->except(['show'])
            ->parameters(['users' => 'user']);

        // Management
        Route::resource('fertilizers', FertilizerController::class)
            ->except(['show'])
            ->parameters(['fertilizers' => 'id']);

        // Fertilizer prices — batch routes before resource
        Route::get('fertilizer-prices/batch-create', [FertilizerPriceController::class, 'batchCreate'])->name('fertilizer-prices.batch-create');
        Route::post('fertilizer-prices/batch', [FertilizerPriceController::class, 'batchStore'])->name('fertilizer-prices.batch-store');
        Route::get('fertilizer-prices/batch-edit', [FertilizerPriceController::class, 'batchEdit'])->name('fertilizer-prices.batch-edit');
        Route::put('fertilizer-prices/batch', [FertilizerPriceController::class, 'batchUpdate'])->name('fertilizer-prices.batch-update');

        Route::resource('fertilizer-prices', FertilizerPriceController::class)
            ->except(['show'])
            ->parameters(['fertilizer-prices' => 'id']);

        // Commodity prices
        Route::resource('maize-prices', MaizePriceController::class)
            ->except(['show'])
            ->parameters(['maize-prices' => 'id']);

        Route::resource('cassava-prices', CassavaPriceController::class)
            ->except(['show'])
            ->parameters(['cassava-prices' => 'id']);

        Route::resource('potato-prices', PotatoPriceController::class)
            ->except(['show'])
            ->parameters(['potato-prices' => 'id']);

        // Starch prices — batch routes must come before the resource to avoid {id} capture
        Route::get('starch-prices/batch-create', [StarchPriceController::class, 'batchCreate'])->name('starch-prices.batch-create');
        Route::post('starch-prices/batch', [StarchPriceController::class, 'batchStore'])->name('starch-prices.batch-store');
        Route::get('starch-prices/batch-edit', [StarchPriceController::class, 'batchEdit'])->name('starch-prices.batch-edit');
        Route::put('starch-prices/batch', [StarchPriceController::class, 'batchUpdate'])->name('starch-prices.batch-update');

        Route::resource('starch-prices', StarchPriceController::class)
            ->except(['show'])
            ->parameters(['starch-prices' => 'id']);

        // Default prices — batch routes before resource
        Route::get('default-prices/batch-create', [DefaultPriceController::class, 'batchCreate'])->name('default-prices.batch-create');
        Route::post('default-prices/batch', [DefaultPriceController::class, 'batchStore'])->name('default-prices.batch-store');
        Route::get('default-prices/batch-edit', [DefaultPriceController::class, 'batchEdit'])->name('default-prices.batch-edit');
        Route::put('default-prices/batch', [DefaultPriceController::class, 'batchUpdate'])->name('default-prices.batch-update');

        Route::resource('default-prices', DefaultPriceController::class)
            ->except(['show'])
            ->parameters(['default-prices' => 'id']);

        // Supporting data
        Route::resource('starch-factories', StarchFactoryController::class)
            ->except(['show'])
            ->parameters(['starch-factories' => 'id']);

        Route::resource('investment-amounts', InvestmentAmountController::class)
            ->except(['show'])
            ->parameters(['investment-amounts' => 'id']);

        // Operation costs — batch routes before resource
        Route::get('operation-costs/batch-create', [OperationCostController::class, 'batchCreate'])->name('operation-costs.batch-create');
        Route::post('operation-costs/batch', [OperationCostController::class, 'batchStore'])->name('operation-costs.batch-store');
        Route::get('operation-costs/batch-edit', [OperationCostController::class, 'batchEdit'])->name('operation-costs.batch-edit');
        Route::put('operation-costs/batch', [OperationCostController::class, 'batchUpdate'])->name('operation-costs.batch-update');

        Route::resource('operation-costs', OperationCostController::class)
            ->except(['show'])
            ->parameters(['operation-costs' => 'id']);

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
    });
});
