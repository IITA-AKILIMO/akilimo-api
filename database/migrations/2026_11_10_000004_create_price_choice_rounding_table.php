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
 * What the price choices are rounded to, per currency (decision D3: 100 for NGN,
 * TZS, RWF and BIF, 5 for GHS). A currency without a row is not rounded. The
 * choice-list views read this table. Seeded from config/price_catalogue.php.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_choice_rounding', function (Blueprint $table) {
            $table->char('currency', 3)->primary();
            $table->unsignedInteger('step');
        });

        foreach ((array) config('price_catalogue.rounding') as $currency => $step) {
            DB::table('price_choice_rounding')->insert(['currency' => $currency, 'step' => $step]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_choice_rounding');
    }
};
