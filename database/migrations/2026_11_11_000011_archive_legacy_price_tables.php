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
 * Keeps a copy of the legacy price tables (the ones renamed *_legacy when their
 * data moved to default_prices and the lookup tables) before the next migration
 * drops them: each table's definition (SHOW CREATE TABLE) and all its rows, as JSON,
 * in legacy_table_archive. That is what lets the drop be rolled back with its data.
 *
 * The archive stays after the drop, so a rollback is possible at any time. Drop
 * it with a later migration once the legacy data is no longer wanted.
 */
return new class extends Migration {
    private const TABLES = [
        'fertilizers_legacy',
        'fertilizer_prices_legacy',
        'cassava_prices_legacy',
        'potato_prices_legacy',
        'maize_prices_legacy',
        'investment_amount_legacy',
        'operation_costs_legacy',
    ];

    public function up(): void
    {
        Schema::create('legacy_table_archive', function (Blueprint $table) {
            $table->string('table_name', 64)->primary();
            $table->longText('definition');
            $table->unsignedInteger('row_count');
            $table->longText('row_data');
            $table->timestamp('archived_at')->useCurrent();
        });

        foreach (self::TABLES as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            $definition = (array) DB::selectOne("SHOW CREATE TABLE `{$name}`");
            $rows = DB::table($name)->get();

            DB::table('legacy_table_archive')->insert([
                'table_name' => $name,
                'definition' => (string) ($definition['Create Table'] ?? ''),
                'row_count' => $rows->count(),
                'row_data' => $rows->toJson(JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_table_archive');
    }
};
