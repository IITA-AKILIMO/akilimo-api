<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The key of the fertiliser a per-bag catalogue price feeds (UREA_TZ,
 * NPK_20_10_10_GH). The catalogue names a fertiliser one way (urea, NPK201010)
 * and the app's option lists another, so the link is stored instead of guessed.
 * The next migration fills it in for the existing rows.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('default_prices', function (Blueprint $table) {
            $table->string('fertilizer_key', 50)->nullable()->after('item');
            $table->index(['country', 'fertilizer_key']);
        });
    }

    public function down(): void
    {
        Schema::table('default_prices', function (Blueprint $table) {
            $table->dropIndex(['country', 'fertilizer_key']);
            $table->dropColumn('fertilizer_key');
        });
    }
};
