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
 * Which catalogue labour price each operation cost list is built around. The
 * operation_costs list names an operation and a type (tillage, manual); the
 * catalogue names the item (manual_ploughing). The operation_costs view reads
 * this table.
 *
 * Weeding with herbicide has no catalogue item, so it has no row and no list
 * until a catalogue price is added and mapped here.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_choice_operations', function (Blueprint $table) {
            $table->string('operation_name', 100);
            $table->string('operation_type', 100);
            $table->string('item', 50);
            $table->primary(['operation_name', 'operation_type']);
        });

        foreach ([
            ['tillage', 'manual', 'manual_ploughing'],
            ['tillage', 'tractor', 'tractor_ploughing'],
            ['ridging', 'manual', 'manual_ridging'],
            ['ridging', 'tractor', 'tractor_ridging'],
            ['weeding', 'manual', 'weeding1'],
        ] as [$name, $type, $item]) {
            DB::table('price_choice_operations')->insert(['operation_name' => $name, 'operation_type' => $type, 'item' => $item]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_choice_operations');
    }
};
