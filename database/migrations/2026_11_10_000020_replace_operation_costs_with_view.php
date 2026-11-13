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
 * operation_costs becomes a view over price_bands: each operation and type in
 * price_choice_operations takes the bands of its catalogue labour price
 * (tillage/manual = manual_ploughing, ridging/tractor = tractor_ridging ...),
 * with the minimum and maximum cost of each band. The -1 "Exact price" row is not
 * part of this list. Same columns as before; ids are band id.
 *
 * Weeding with herbicide has no catalogue item and so no rows until one is added
 * to the catalogue and mapped in price_choice_operations.
 *
 * The old table is kept as operation_costs_legacy until a later migration drops it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::rename('operation_costs', 'operation_costs_legacy');

        DB::statement(<<<'SQL'
CREATE VIEW operation_costs AS
SELECT b.id AS id,
       o.operation_name AS operation_name,
       o.operation_type AS operation_type,
       b.country AS country_code,
       b.min_local_price AS min_cost,
       b.max_local_price AS max_cost,
       1 AS is_active,
       b.updated_at AS created_at,
       b.updated_at AS updated_at
FROM price_bands b
JOIN price_choice_operations o ON o.item = b.item
WHERE b.step_order > 0
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS operation_costs');
        Schema::rename('operation_costs_legacy', 'operation_costs');
    }
};
