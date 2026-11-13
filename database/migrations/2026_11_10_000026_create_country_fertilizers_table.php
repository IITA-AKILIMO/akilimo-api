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
 * Which fertiliser is sold in which country, and how the app uses it there. One
 * row per fertiliser type and country (enforced); its fertilizer key is the type,
 * an underscore and the country (UREA_TZ), so the key is not stored and cannot
 * disagree with them.
 *
 * fertilizer_type references fertilizer_types.type and follows a rename. Row ids
 * are the ids the fertilizers rows had, so ids the apps and the option lists
 * already hold stay valid.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_fertilizers', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->primary();
            $table->string('fertilizer_type', 50);
            $table->string('country', 5);
            $table->string('use_case', 50)->default('ALL');
            $table->boolean('cis')->default(false);
            $table->boolean('cim')->default(false);
            $table->boolean('available')->default(true);
            $table->integer('sort_order')->nullable();
            $table->timestamps();

            $table->unique(['fertilizer_type', 'country']);
            $table->foreign('fertilizer_type')->references('type')->on('fertilizer_types')->cascadeOnUpdate();
        });

        $taken = [];

        foreach (DB::table('fertilizers')->orderBy('id')->get() as $row) {
            $pair = strtolower((string) $row->type) . '|' . strtolower((string) $row->country);

            // one row per type and country; the data has no repeats today
            if (isset($taken[$pair])) {
                continue;
            }

            $taken[$pair] = true;

            DB::table('country_fertilizers')->insert([
                'id' => $row->id,
                'fertilizer_type' => DB::table('fertilizer_types')->where('type', $row->type)->value('type'),
                'country' => $row->country,
                'use_case' => $row->use_case ?? 'ALL',
                'cis' => (int) $row->cis,
                'cim' => (int) $row->cim,
                'available' => (int) $row->available,
                'sort_order' => $row->sort_order,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('country_fertilizers');
    }
};
