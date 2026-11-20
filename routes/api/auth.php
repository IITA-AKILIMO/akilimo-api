<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\McpKeyController;
use Illuminate\Support\Facades\Route;

// ── Authentication ────────────────────────────────────────────────────────────
Route::middleware('throttle:auth')->prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.token');
});

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
