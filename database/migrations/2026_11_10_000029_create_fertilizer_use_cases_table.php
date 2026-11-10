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
 * What a fertiliser is offered for in a country (use_case), as a list instead of
 * typed text. The admin form picks from it and the next migration makes
 * country_fertilizers.use_case a foreign key to it, so a typo (Fr, FR ) cannot
 * create a use case nothing filters on.
 *
 * Seeded with the values in use (FR, CIM, CIS) and ALL, plus any other value
 * already in country_fertilizers, so existing rows stay valid. The labels are
 * descriptions to be confirmed by the owner; edit them in this table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('fertilizer_use_cases', function (Blueprint $table) {
            $table->string('value', 50)->primary();
            $table->string('label', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        $cases = [
            'FR' => 'Fertilizer recommendation',
            'CIM' => 'Cassava and maize intercrop',
            'CIS' => 'Cassava and sweet potato intercrop',
            'ALL' => 'All uses',
        ];

        // Anything else already in use keeps working; its label is the value until someone names it
        $known = array_change_key_case($cases, CASE_LOWER);
        foreach (DB::table('country_fertilizers')->whereNotNull('use_case')->distinct()->pluck('use_case') as $value) {
            if (! isset($known[strtolower($value)])) {
                $cases[$value] = $value;
                $known[strtolower($value)] = $value;
            }
        }

        $i = 0;
        foreach ($cases as $value => $label) {
            DB::table('fertilizer_use_cases')->insert(['value' => $value, 'label' => $label, 'sort_order' => ++$i]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fertilizer_use_cases');
    }
};
