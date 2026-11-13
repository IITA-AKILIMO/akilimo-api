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
 * The units maize grain is sold in, for a client to offer as the unit of sale
 * (the same idea as cassava_units). Researched for Nigeria, the only country with
 * maize prices:
 *
 *  - 100 kg: the standard bag. Wholesale and market prices are quoted per 100 kg bag
 *    (FEWS NET Nigeria price bulletins, market reports from Gombe, Kaduna and Niger
 *    State), so it is the default.
 *  - 1 kg: how the exchange and retail quote it (AFEX spot price per kg).
 *  - 1,000 kg: the tonne, for bulk and industrial sales and for trade.
 *  - 50 kg: the half bag the app already offers for smaller lots.
 *
 * Local measures (mudu, kongo, derica, paint bucket) are common in retail but have
 * no fixed weight: sources disagree by an order of magnitude (a mudu is 0.17 to
 * 2 kg) and it depends on the region and on how the seller fills it, so they are
 * not stored as units. Add one here only with a weight the owner confirms.
 *
 * The labels are the names of the app's own units of sale (ONE_KG, FIFTY_KG,
 * HUNDRED_KG, THOUSAND_KG), so the app maps a unit by its label as it does for
 * cassava. Prices in the maize list are per tonne; a client converts them to the
 * chosen unit as average x weight / 1000.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('maize_units', function (Blueprint $table) {
            $table->id();
            $table->string('label', 50)->unique();
            $table->double('unit_weight');            // kilograms
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ([
            ['ONE_KG', 1, 'Per kilogram: how exchange and retail prices are quoted', 1, false],
            ['FIFTY_KG', 50, 'Half bag, for smaller lots', 2, false],
            ['HUNDRED_KG', 100, 'Standard bag: wholesale and market prices are quoted per 100 kg bag', 3, true],
            ['THOUSAND_KG', 1000, 'Tonne: bulk and industrial sales; the unit the price list is in', 4, false],
        ] as [$label, $weight, $description, $order, $default]) {
            DB::table('maize_units')->insert([
                'label' => $label,
                'unit_weight' => $weight,
                'description' => $description,
                'sort_order' => $order,
                'is_default' => $default,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maize_units');
    }
};
