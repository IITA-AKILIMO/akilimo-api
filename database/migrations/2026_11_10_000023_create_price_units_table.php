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
 * The units a catalogue price can be in. The admin price forms offer them as a
 * list and validation accepts only these, so a typo (per_kilo, Per Bag) cannot
 * reach a price list. Held in the database so they can be managed there, instead
 * of in config.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_units', function (Blueprint $table) {
            $table->string('value', 15)->primary();
            $table->string('label', 50);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        foreach ([
            ['per_bag', 'Per bag'],
            ['per_acre', 'Per acre'],
            ['per_tonne', 'Per tonne'],
            ['per_kg', 'Per kg'],
            ['per_cob', 'Per cob'],
        ] as $i => [$value, $label]) {
            DB::table('price_units')->insert(['value' => $value, 'label' => $label, 'sort_order' => $i + 1]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_units');
    }
};
