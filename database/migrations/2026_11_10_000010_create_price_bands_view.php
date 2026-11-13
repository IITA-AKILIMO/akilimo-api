<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * price_bands: the range-list rule, written once. For every active, national,
 * effective catalogue price (default_prices) it gives one band per step in
 * price_choice_steps, and one marker row (step_order 0) for "Exact price".
 *
 * A band is the country's reference price changed by the step plus or minus
 * band_half_pct, rounded per price_choice_rounding, so the bands are contiguous
 * and the average the app sends is the step. The reference is the subsidised
 * price when the country's basis is subsidised and one exists (decision D1),
 * otherwise the open-market price.
 *
 * The crop views (cassava_prices, potato_prices and later maize_prices) are thin
 * filters over this view by catalogue item, so a change to the rule is one
 * migration. A row id is catalogue id * 10 + step; the marker is
 * 1,000,000 + catalogue id * 10.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE VIEW price_bands AS
SELECT x.id * 10 + x.step_order AS id,
       x.item AS item,
       x.country AS country,
       x.step_order AS step_order,
       x.lo AS min_local_price,
       x.hi AS max_local_price,
       (x.step_order = (SELECT MIN(step_order) FROM price_choice_steps)) AS is_first,
       (x.step_order = (SELECT MAX(step_order) FROM price_choice_steps)) AS is_last,
       x.updated_at AS updated_at
FROM (
    SELECT d.id, d.item, d.country, d.updated_at, s.step_order,
           CAST(ROUND(
               (CASE WHEN c.default_price_basis = 'subsidised' AND d.subsidised_price IS NOT NULL THEN d.subsidised_price ELSE d.price END)
               * (1 + (s.change_pct - s.band_half_pct) / 100) / COALESCE(r.step, 1)
           ) * COALESCE(r.step, 1) AS DECIMAL(10, 2)) AS lo,
           CAST(ROUND(
               (CASE WHEN c.default_price_basis = 'subsidised' AND d.subsidised_price IS NOT NULL THEN d.subsidised_price ELSE d.price END)
               * (1 + (s.change_pct + s.band_half_pct) / 100) / COALESCE(r.step, 1)
           ) * COALESCE(r.step, 1) AS DECIMAL(10, 2)) AS hi
    FROM default_prices d
    JOIN countries c ON c.code = d.country
    CROSS JOIN price_choice_steps s
    LEFT JOIN price_choice_rounding r ON r.currency = d.currency
    WHERE d.unit <> 'per_bag'
      AND d.status = 'active'
      AND d.region IS NULL
      AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
) x
WHERE x.lo > 0 AND x.hi > x.lo
UNION ALL
SELECT 1000000 + d.id * 10, d.item, d.country, 0, -1, -1, 0, 0, d.updated_at
FROM default_prices d
WHERE d.unit <> 'per_bag'
  AND d.status = 'active'
  AND d.region IS NULL
  AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS price_bands');
    }
};
