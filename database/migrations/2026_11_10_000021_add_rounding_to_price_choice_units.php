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
 * An item can override the currency's rounding step. A price per cob is 15 to 60
 * naira, so rounding it to 100 (the naira step) would round every band to zero;
 * price_choice_units.rounding gives such items their own step. The next migration
 * makes price_bands read it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('price_choice_units', function (Blueprint $table) {
            $table->unsignedInteger('rounding')->nullable()->after('quantity');
        });

        // quantity 1: shown as it is, rounded to 5
        DB::table('price_choice_units')->insert(['item' => 'maize_freshCob', 'quantity' => 1, 'rounding' => 5]);
    }

    public function down(): void
    {
        DB::table('price_choice_units')->where('item', 'maize_freshCob')->delete();

        Schema::table('price_choice_units', function (Blueprint $table) {
            $table->dropColumn('rounding');
        });
    }
};
