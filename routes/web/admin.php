<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\RequestLogController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

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

        // Prices and fertilizers, then reference data and other lookup lists
        require __DIR__ . '/admin/pricing.php';
        require __DIR__ . '/admin/lookup-data.php';
    });
});
