<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Controllers\Web\AccountDeletionController;
use Illuminate\Support\Facades\Route;

// Public page for the Google Play "request account deletion" link: /account/delete (on the web host, next to the playground and admin)
Route::prefix('account/delete')->name('account-deletion.')->middleware('throttle:sensitive')->group(function () {
    Route::get('/', [AccountDeletionController::class, 'show'])->name('show');
    Route::post('/', [AccountDeletionController::class, 'request'])->name('request');
    Route::post('/confirm', [AccountDeletionController::class, 'confirm'])->name('confirm');
});
