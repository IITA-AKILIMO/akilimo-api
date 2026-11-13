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
 * Which fertiliser a per-bag catalogue price is for, as the fertiliser's type
 * code (UREA, NPK_20_10_10). The catalogue names a fertiliser one way (urea,
 * NPK201010) and the app's option lists another, so the link is stored instead
 * of guessed. With the row's country it gives the fertiliser key (UREA_TZ), so
 * the key itself is not stored.
 *
 * The next migration fills it in for the existing rows; migration 000028 makes it
 * a foreign key to fertilizer_types once that table exists.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('default_prices', function (Blueprint $table) {
            $table->string('fertilizer_type', 50)->nullable()->after('item');
            $table->index(['country', 'fertilizer_type']);
        });
    }

    public function down(): void
    {
        Schema::table('default_prices', function (Blueprint $table) {
            $table->dropIndex(['country', 'fertilizer_type']);
            $table->dropColumn('fertilizer_type');
        });
    }
};
