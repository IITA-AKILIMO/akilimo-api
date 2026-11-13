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
 * The kinds of thing a catalogue price can be for, as a list: fertilizers, produce
 * (what a farmer sells), labour (hand work), machinery (tractor hire), the
 * investment amount, and other. The next migration links each price item to one so
 * Default prices can be filtered by it. The labels are descriptions to confirm with
 * the owner; edit them in this table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_categories', function (Blueprint $table) {
            $table->string('value', 50)->primary();
            $table->string('label', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        $i = 0;
        foreach ([
            'FERTILIZER' => 'Fertilizers',
            'PRODUCE' => 'Produce (crops sold)',
            'LABOUR' => 'Labour (hand work)',
            'MACHINERY' => 'Machinery (tractor hire)',
            'INVESTMENT' => 'Investment amount',
            'OTHER' => 'Other',
        ] as $value => $label) {
            DB::table('price_categories')->insert(['value' => $value, 'label' => $label, 'sort_order' => ++$i]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_categories');
    }
};
