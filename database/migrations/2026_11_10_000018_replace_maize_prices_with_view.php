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
 * maize_prices becomes a thin view over price_bands: grain bands from the
 * maize_grain catalogue price (per kg, shown per 50 kg through
 * price_choice_units) and fresh-cob bands from maize_freshCob (per cob), each
 * with its -1 "Exact price" row. Same columns as before, plus produce_type
 * (grain or cob). Ids are catalogue id * 10 + step.
 *
 * The old table is kept as maize_prices_legacy until a later migration drops it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::rename('maize_prices', 'maize_prices_legacy');

        DB::statement(<<<'SQL'
CREATE VIEW maize_prices AS
SELECT b.id AS id,
       b.country AS country,
       b.min_local_price AS min_local_price,
       b.max_local_price AS max_local_price,
       0 AS min_usd,
       0 AS max_usd,
       b.is_first AS min_price,
       b.is_last AS max_price,
       1 AS price_active,
       1 AS sort_order,
       b.updated_at AS created_at,
       b.updated_at AS updated_at,
       (CASE b.item WHEN 'maize_grain' THEN 'grain' ELSE 'cob' END) AS produce_type
FROM price_bands b
WHERE b.item IN ('maize_grain', 'maize_freshCob')
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS maize_prices');
        Schema::rename('maize_prices_legacy', 'maize_prices');
    }
};
