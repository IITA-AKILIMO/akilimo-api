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
 * The fertilisers, once each. The fertilizers table repeated a fertiliser's
 * type, label, name and bag weight for every country that sells it (UREA four
 * times, DAP four times). This table holds those facts one time; the next
 * migration links a type to a country.
 *
 *  - type (the primary key): the code used in the fertiliser key (UREA,
 *    NPK_20_10_10). It is the natural key: everything that names a fertiliser
 *    (the key, the admin form, the API) already uses it, and it cascades if it is
 *    ever renamed.
 *  - label: how the catalogue and the engine name it (urea, NPK201010)
 *  - name: what a person reads (Urea, NPK 20:10:10)
 *  - weight: the usual bag weight in kg
 *
 * Backfilled from fertilizers; the data was consistent (one label, name and
 * weight per type), and where it is not the first row by id wins.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('fertilizer_types', function (Blueprint $table) {
            $table->string('type', 50)->primary();
            $table->string('label', 50)->nullable();
            $table->string('name', 255);
            $table->unsignedSmallInteger('weight')->default(50);
            $table->timestamps();
        });

        // The database compares codes without regard to case, so UREA and urea are one type
        $seen = [];

        foreach (DB::table('fertilizers')->orderBy('id')->get() as $row) {
            $key = strtolower((string) $row->type);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            DB::table('fertilizer_types')->insert([
                'type' => $row->type,
                'label' => $row->fertilizer_label,
                'name' => $row->name,
                'weight' => $row->weight,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fertilizer_types');
    }
};
