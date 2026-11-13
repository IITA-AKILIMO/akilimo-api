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
 * investment_amount becomes a view: for each country with an active
 * investment_amount catalogue price (the reference amount per acre), one row per
 * multiple in price_choice_multiples (1, 2, 3, 4, 6 times it), plus the 0 row
 * the app shows first. Same columns as before; ids are catalogue id * 10 + step.
 *
 * The old table is kept as investment_amount_legacy until a later migration
 * drops it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::rename('investment_amount', 'investment_amount_legacy');

        DB::statement(<<<'SQL'
CREATE VIEW investment_amount AS
SELECT d.id * 10 + m.step_order AS id,
       d.country AS country,
       CAST(ROUND(d.price * m.multiple, 2) AS DECIMAL(10, 2)) AS investment_amount,
       'acre' AS area_unit,
       1 AS price_active,
       m.step_order AS sort_order,
       d.updated_at AS created_at,
       d.updated_at AS updated_at
FROM default_prices d
CROSS JOIN price_choice_multiples m
WHERE d.item = 'investment_amount'
  AND d.status = 'active'
  AND d.region IS NULL
  AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
UNION ALL
SELECT 1000000 + d.id * 10, d.country, 0, 'acre', 1, 0, d.updated_at, d.updated_at
FROM default_prices d
WHERE d.item = 'investment_amount'
  AND d.status = 'active'
  AND d.region IS NULL
  AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS investment_amount');
        Schema::rename('investment_amount_legacy', 'investment_amount');
    }
};
