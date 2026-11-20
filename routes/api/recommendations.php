<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

// ── Recommendations ───────────────────────────────────────────────────────────
// Route::middleware(['throttle:compute', 'auth.token:recommendations:compute'])->group(function () {
//    Route::post('v1/recommendations/compute', [RecommendationController::class, 'computeRecommendations']);
// });

// Open to everyone; a signed-in farmer's token (optional) links the saved advice to their account
Route::middleware(['throttle:compute', 'farmer.optional'])->group(function () {
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
