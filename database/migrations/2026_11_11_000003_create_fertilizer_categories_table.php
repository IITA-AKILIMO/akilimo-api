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
 * The kinds of fertiliser, as a list instead of typed text: STRAIGHT (one nutrient,
 * such as urea), COMPOUND (more than one nutrient, such as DAP and the NPK blends)
 * and OTHER for anything not yet classified. The next migration links each
 * fertiliser to one. The labels are descriptions to be confirmed by the owner; edit
 * them in this table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('fertilizer_categories', function (Blueprint $table) {
            $table->string('value', 50)->primary();
            $table->string('label', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        $i = 0;
        foreach ([
            'STRAIGHT' => 'Straight (one nutrient)',
            'COMPOUND' => 'Compound (more than one nutrient)',
            'OTHER' => 'Other',
        ] as $value => $label) {
            DB::table('fertilizer_categories')->insert(['value' => $value, 'label' => $label, 'sort_order' => ++$i]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fertilizer_categories');
    }
};
