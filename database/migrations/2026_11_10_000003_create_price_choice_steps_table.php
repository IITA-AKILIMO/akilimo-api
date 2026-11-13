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
 * The steps of the price choices the app shows (decision D3): each option is the
 * country's reference price changed by change_pct, and for the range lists
 * (cassava, sweet potato) each band is that value plus or minus band_half_pct.
 * The choice-list views read this table, so changing a row here changes every
 * list at once. Seeded from config/price_catalogue.php.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_choice_steps', function (Blueprint $table) {
            $table->unsignedTinyInteger('step_order')->primary();
            $table->decimal('change_pct', 6, 2);      // -30 = 30 % below the reference price
            $table->decimal('band_half_pct', 6, 2);   // half the width of a range-list band, in % of the reference
        });

        $steps = array_values((array) config('price_catalogue.choice_steps'));
        sort($steps);
        $half = count($steps) > 1
            ? min(array_map(fn ($i) => $steps[$i + 1] - $steps[$i], range(0, count($steps) - 2))) / 2
            : 0.1;

        foreach ($steps as $i => $step) {
            DB::table('price_choice_steps')->insert([
                'step_order' => $i + 1,
                'change_pct' => $step * 100,
                'band_half_pct' => $half * 100,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_choice_steps');
    }
};
