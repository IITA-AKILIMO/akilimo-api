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
 * fertilizer_prices becomes a database view computed live from the price
 * catalogue (default_prices), so the options the app shows cannot drift from it
 * and nothing has to regenerate them. The view keeps the old name and columns,
 * so the model, resource and API response are unchanged.
 *
 *  - Each active, national, effective per-bag catalogue price with a
 *    fertilizer_type gives one option per step in price_choice_steps, rounded per
 *    price_choice_rounding. The country's default_price_basis picks open-market
 *    or subsidised as the reference (decision D1).
 *  - Every fertiliser also gets the two marker rows the app relies on: "I don't
 *    know" (price 0) and "Exact price" (negative).
 *  - Row ids come from the catalogue id (id * 10 + step), so they stay the same
 *    when a price changes; markers use ids from 1,000,000. A farmer who picked an
 *    option before this change has to pick again once.
 *
 * The old table is kept as fertilizer_prices_legacy until the view has been
 * checked in production; a later migration drops it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::rename('fertilizer_prices', 'fertilizer_prices_legacy');

        DB::statement(<<<'SQL'
CREATE VIEW fertilizer_prices AS
SELECT x.id * 10 + x.step_order AS id,
       x.country AS country,
       x.fertilizer_key AS fertilizer_key,
       x.price AS min_price,
       x.price AS max_price,
       x.price AS price_per_bag,
       1 AS price_active,
       x.step_order AS sort_order,
       '' AS `desc`,
       x.updated_at AS created_at,
       x.updated_at AS updated_at
FROM (
    SELECT d.id, d.country, CONCAT(d.fertilizer_type, '_', d.country) AS fertilizer_key, d.updated_at, s.step_order, s.change_pct,
           CAST(ROUND(
               (CASE WHEN c.default_price_basis = 'subsidised' AND d.subsidised_price IS NOT NULL THEN d.subsidised_price ELSE d.price END)
               * (1 + s.change_pct / 100) / COALESCE(r.step, 1)
           ) * COALESCE(r.step, 1) AS DECIMAL(10, 2)) AS price
    FROM default_prices d
    JOIN countries c ON c.code = d.country
    CROSS JOIN price_choice_steps s
    LEFT JOIN price_choice_rounding r ON r.currency = d.currency
    WHERE d.unit = 'per_bag'
      AND d.fertilizer_type IS NOT NULL
      AND d.status = 'active'
      AND d.region IS NULL
      AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
) x
WHERE x.price > 0
UNION ALL
SELECT 1000000 + f.id * 10, f.country, f.fertilizer_key, 0, 0, 0, 1, 0, 'I don''t know', f.updated_at, f.updated_at
FROM fertilizers f
UNION ALL
SELECT 1000000 + f.id * 10 + 5, f.country, f.fertilizer_key, -1, -1, -1, 1, 5, 'Exact price', f.updated_at, f.updated_at
FROM fertilizers f
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS fertilizer_prices');
        Schema::rename('fertilizer_prices_legacy', 'fertilizer_prices');
    }
};
