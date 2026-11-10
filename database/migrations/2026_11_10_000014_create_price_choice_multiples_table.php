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
 * The investment choices are multiples of one reference amount per acre per
 * country (the investment_amount catalogue price): the existing lists were
 * 1, 2, 3, 4 and 6 times it (TZ 57,550, 115,100, 172,650, 230,200, 345,300).
 * The investment_amount view reads this table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_choice_multiples', function (Blueprint $table) {
            $table->unsignedTinyInteger('step_order')->primary();
            $table->decimal('multiple', 6, 2);
        });

        foreach ([1, 2, 3, 4, 6] as $i => $multiple) {
            DB::table('price_choice_multiples')->insert(['step_order' => $i + 1, 'multiple' => $multiple]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_choice_multiples');
    }
};
