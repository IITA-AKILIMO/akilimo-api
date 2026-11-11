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
 * Each fertiliser has a category (fertilizer_types.category, a foreign key to
 * fertilizer_categories). Backfilled from the type code: UREA, TSP, SSP, MOP and CAN are
 * straight; DAP, MAP, every NPK blend and the named blends (Nafaka Plus, YaraMila Unik) are
 * compound; anything else (organic, lime) is OTHER until
 * someone classifies it on the fertiliser.
 */
return new class extends Migration {
    private const STRAIGHT = ['UREA', 'TSP', 'SSP', 'MOP', 'KCL', 'CAN'];

    public function up(): void
    {
        Schema::table('fertilizer_types', function (Blueprint $table) {
            $table->string('category', 50)->default('OTHER')->after('weight');
        });

        foreach (DB::table('fertilizer_types')->pluck('type') as $type) {
            $code = strtoupper((string) $type);
            $base = preg_replace('/_\d+$/', '', $code);

            $category = match (true) {
                in_array($base, self::STRAIGHT, true) => 'STRAIGHT',
                in_array($base, ['DAP', 'MAP'], true), str_starts_with($code, 'NPK'), in_array($base, ['NAFAKAPLUS', 'YARAMILA_UNIK'], true) => 'COMPOUND',
                default => 'OTHER',
            };

            DB::table('fertilizer_types')->where('type', $type)->update(['category' => $category]);
        }

        Schema::table('fertilizer_types', function (Blueprint $table) {
            $table->foreign('category')->references('value')->on('fertilizer_categories')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('fertilizer_types', function (Blueprint $table) {
            $table->dropForeign(['category']);
            $table->dropColumn('category');
        });
    }
};

