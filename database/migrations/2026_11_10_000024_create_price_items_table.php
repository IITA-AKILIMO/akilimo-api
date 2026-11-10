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
 * The items a catalogue price can be for, and the unit each is priced in. The
 * admin form offers them as a list and fills in the unit when one is chosen, and
 * validation accepts only these, so an item the price lists do not know (cassUp,
 * Urea ) cannot be added by mistake. A new item is a row here.
 *
 * Seeded with the labour, crop and investment items, then every item already in
 * the catalogue (with its own unit) and every fertiliser (per bag), so existing
 * prices stay editable.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_items', function (Blueprint $table) {
            $table->string('item', 50)->primary();
            $table->string('unit', 15);
            $table->foreign('unit')->references('value')->on('price_units')->cascadeOnUpdate();
        });

        $items = [
            'manual_ploughing' => 'per_acre',
            'manual_harrowing' => 'per_acre',
            'manual_ridging' => 'per_acre',
            'tractor_ploughing' => 'per_acre',
            'tractor_harrowing' => 'per_acre',
            'tractor_ridging' => 'per_acre',
            'weeding1' => 'per_acre',
            'weeding2' => 'per_acre',
            'investment_amount' => 'per_acre',
            'cassUP' => 'per_tonne',
            'sweetPotato_tuber' => 'per_tonne',
            'sweetPotato_flour' => 'per_tonne',
            'maize_grain' => 'per_kg',
            'maize_freshCob' => 'per_cob',
        ];

        // The fertilisers in the default price list, per bag
        foreach ([
            'urea', 'CAN', 'DAP', 'MOP', 'SSP', 'TSP',
            'NPK112221', 'NPK123017', 'NPK151515', 'NPK152020', 'NPK171717', 'NPK201010', 'NPK201216', 'NPK23105', 'NPK251010',
            'FOMIBAGARA', 'FOMIIMBURA', 'FOMITOTAHAZA',
        ] as $fertiliser) {
            $items[$fertiliser] = 'per_bag';
        }

        $units = DB::table('price_units')->pluck('value')->all();

        // Items already in the catalogue keep the unit they have, when it is a known unit
        $seen = array_change_key_case($items, CASE_LOWER);
        foreach (DB::table('default_prices')->select('item', 'unit')->distinct()->get() as $row) {
            if (! isset($seen[strtolower($row->item)]) && in_array($row->unit, $units, true)) {
                $items[$row->item] = $row->unit;
                $seen[strtolower($row->item)] = $row->unit;
            }
        }

        // Fertilisers are priced per bag; the catalogue names them by their label (urea, NPK201010).
        // The database compares names without regard to case, so UREA and urea are one item.
        $lower = array_change_key_case($items, CASE_LOWER);
        foreach (DB::table('fertilizers')->whereNotNull('fertilizer_label')->distinct()->pluck('fertilizer_label') as $label) {
            if (! isset($lower[strtolower($label)])) {
                $items[$label] = 'per_bag';
                $lower[strtolower($label)] = 'per_bag';
            }
        }

        foreach ($items as $item => $unit) {
            DB::table('price_items')->insert(['item' => $item, 'unit' => $unit]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_items');
    }
};
