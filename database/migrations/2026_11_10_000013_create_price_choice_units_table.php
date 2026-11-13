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
 * Some catalogue prices are per kilogram while the app asks the farmer for the
 * price of a unit weight (the request's maizeUW, 50 kg by default). This table
 * says how many units of the catalogue price the app's choices are shown for:
 * maize_grain is per kg and shown per 50 kg, so a catalogue price of 230 gives
 * choices around 11,500. An item without a row is shown as it is. The price_bands
 * view reads it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_choice_units', function (Blueprint $table) {
            $table->string('item', 50)->primary();
            $table->unsignedInteger('quantity');   // the catalogue price is multiplied by this
        });

        DB::table('price_choice_units')->insert(['item' => 'maize_grain', 'quantity' => 50]);
    }

    public function down(): void
    {
        Schema::dropIfExists('price_choice_units');
    }
};
