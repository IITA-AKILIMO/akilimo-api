<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Admin\FertilizerController;
use App\Http\Controllers\Admin\FertilizerTypeController;
use App\Http\Controllers\Admin\PriceCatalogueController;
use App\Http\Controllers\Admin\PriceListController;
use App\Http\Controllers\Admin\StarchPriceController;
use Illuminate\Support\Facades\Route;

// Admin-only; required inside the admin group in routes/web/admin.php

// Management
Route::resource('fertilizers', FertilizerController::class)
    ->except(['show'])
    ->parameters(['fertilizers' => 'id']);

// What the app's price lists contain right now (read-only)
Route::get('price-lists', [PriceListController::class, 'index'])->name('price-lists.index');

// A new fertilizer (its type code is generated from the name)
Route::get('fertilizer-types/create', [FertilizerTypeController::class, 'create'])->name('fertilizer-types.create');
Route::post('fertilizer-types', [FertilizerTypeController::class, 'store'])->name('fertilizer-types.store');
// Change a fertilizer's own details (name, bag weight, category) for every country at once
Route::get('fertilizer-types/{type}/edit', [FertilizerTypeController::class, 'edit'])->name('fertilizer-types.edit');
Route::put('fertilizer-types/{type}', [FertilizerTypeController::class, 'update'])->name('fertilizer-types.update');

// Starch prices — batch routes must come before the resource to avoid {id} capture
Route::get('starch-prices/batch-create', [StarchPriceController::class, 'batchCreate'])->name('starch-prices.batch-create');
Route::post('starch-prices/batch', [StarchPriceController::class, 'batchStore'])->name('starch-prices.batch-store');
Route::get('starch-prices/batch-edit', [StarchPriceController::class, 'batchEdit'])->name('starch-prices.batch-edit');
Route::put('starch-prices/batch', [StarchPriceController::class, 'batchUpdate'])->name('starch-prices.batch-update');

Route::resource('starch-prices', StarchPriceController::class)
    ->except(['show'])
    ->parameters(['starch-prices' => 'id']);

// Price catalogue: batch and workflow routes before the resource
Route::get('price-catalogue/batch-create', [PriceCatalogueController::class, 'batchCreate'])->name('price-catalogue.batch-create');
Route::post('price-catalogue/batch', [PriceCatalogueController::class, 'batchStore'])->name('price-catalogue.batch-store');
Route::get('price-catalogue/batch-edit', [PriceCatalogueController::class, 'batchEdit'])->name('price-catalogue.batch-edit');
Route::put('price-catalogue/batch', [PriceCatalogueController::class, 'batchUpdate'])->name('price-catalogue.batch-update');

Route::post('price-catalogue/{id}/activate', [PriceCatalogueController::class, 'activate'])->name('price-catalogue.activate');
Route::post('price-catalogue/{id}/deactivate', [PriceCatalogueController::class, 'deactivate'])->name('price-catalogue.deactivate');
Route::post('price-catalogue/{id}/confirm', [PriceCatalogueController::class, 'confirm'])->name('price-catalogue.confirm');

Route::resource('price-catalogue', PriceCatalogueController::class)
    ->except(['show'])
    ->parameters(['price-catalogue' => 'id']);
