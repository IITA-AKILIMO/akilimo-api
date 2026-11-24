<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Admin\CassavaUnitController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CropController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\ReferenceDataController;
use App\Http\Controllers\Admin\StarchFactoryController;
use App\Http\Controllers\Admin\TrafficExclusionController;
use App\Http\Controllers\Admin\TranslationController;
use Illuminate\Support\Facades\Route;

// Admin-only; required inside the admin group in routes/web/admin.php

// Reference data and price settings: the lists the price forms choose from and the rules the app's lists use
Route::get('reference', [ReferenceDataController::class, 'hub'])->name('reference.hub');
Route::get('reference/{slug}', [ReferenceDataController::class, 'index'])->name('reference.index');
Route::get('reference/{slug}/create', [ReferenceDataController::class, 'create'])->name('reference.create');
Route::post('reference/{slug}', [ReferenceDataController::class, 'store'])->name('reference.store');
Route::post('reference/{slug}/preview', [ReferenceDataController::class, 'preview'])->name('reference.preview');
Route::get('reference/{slug}/edit', [ReferenceDataController::class, 'edit'])->name('reference.edit');
Route::put('reference/{slug}', [ReferenceDataController::class, 'update'])->name('reference.update');
Route::delete('reference/{slug}', [ReferenceDataController::class, 'destroy'])->name('reference.destroy');

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
