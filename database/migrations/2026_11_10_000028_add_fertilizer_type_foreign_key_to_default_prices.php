<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * default_prices.fertilizer_type becomes a foreign key to fertilizer_types.type,
 * so the database refuses a catalogue price for a fertiliser that does not exist,
 * follows a rename of a type, and refuses to delete a type that still has
 * catalogue prices.
 *
 * Anything that no longer matches a type (a type removed since migration 000002)
 * is cleared first, so the key can be added.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('default_prices')
            ->whereNotNull('fertilizer_type')
            ->whereNotIn('fertilizer_type', DB::table('fertilizer_types')->select('type'))
            ->update(['fertilizer_type' => null]);

        Schema::table('default_prices', function (Blueprint $table) {
            $table->foreign('fertilizer_type')->references('type')->on('fertilizer_types')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('default_prices', function (Blueprint $table) {
            $table->dropForeign(['fertilizer_type']);
        });
    }
};
