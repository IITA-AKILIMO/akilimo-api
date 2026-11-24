<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\UserFeedbackController;
use Illuminate\Support\Facades\Route;

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
