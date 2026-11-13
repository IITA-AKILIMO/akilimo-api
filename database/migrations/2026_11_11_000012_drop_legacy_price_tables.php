<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the legacy price tables. Their data lives in default_prices and the lookup
 * tables (see the migrations that renamed them), and nothing reads them: no view
 * and no foreign key refers to them. The previous migration archived each table,
 * so rolling this back recreates the tables with all their rows.
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
        $archived = DB::table('legacy_table_archive')->pluck('row_count', 'table_name');

        foreach (self::TABLES as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            // Never drop a table whose copy is missing or short, so a rollback can always bring it back
            if (! isset($archived[$name]) || (int) $archived[$name] !== DB::table($name)->count()) {
                throw new RuntimeException("{$name} is not fully archived in legacy_table_archive; refusing to drop it.");
            }
        }

        foreach (self::TABLES as $name) {
            Schema::dropIfExists($name);
        }
    }

    public function down(): void
    {
        foreach (DB::table('legacy_table_archive')->get() as $archive) {
            $name = (string) $archive->table_name;

            if (Schema::hasTable($name)) {
                continue;
            }

            DB::statement((string) $archive->definition);

            foreach (array_chunk(json_decode((string) $archive->row_data, true) ?: [], 200) as $chunk) {
                DB::table($name)->insert($chunk);
            }
        }
    }
};
