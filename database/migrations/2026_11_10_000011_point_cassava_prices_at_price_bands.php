<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * cassava_prices becomes a thin view over price_bands (migration
 * 2026_11_10_000010) for the cassUP catalogue item. Same columns, ids and values
 * as before; the band rule now lives in one place.
 *
 * down() restores the self-contained definition from migration 000006.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW cassava_prices AS
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
       b.updated_at AS updated_at
FROM price_bands b
WHERE b.item = 'cassUP'
SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW cassava_prices AS
SELECT x.id * 10 + x.step_order AS id,
       x.country AS country,
       x.lo AS min_local_price,
       x.hi AS max_local_price,
       0 AS min_usd,
       0 AS max_usd,
       (x.step_order = (SELECT MIN(step_order) FROM price_choice_steps)) AS min_price,
       (x.step_order = (SELECT MAX(step_order) FROM price_choice_steps)) AS max_price,
       1 AS price_active,
       1 AS sort_order,
       x.updated_at AS created_at,
       x.updated_at AS updated_at
FROM (
    SELECT d.id, d.country, d.updated_at, s.step_order,
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
    WHERE d.item = 'cassUP'
      AND d.status = 'active'
      AND d.region IS NULL
      AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
) x
WHERE x.lo > 0 AND x.hi > x.lo
UNION ALL
SELECT 1000000 + d.id * 10, d.country, -1, -1, 0, 0, 0, 0, 1, 1, d.updated_at, d.updated_at
FROM default_prices d
WHERE d.item = 'cassUP'
  AND d.status = 'active'
  AND d.region IS NULL
  AND (d.effective_from IS NULL OR d.effective_from <= CURDATE())
SQL);
    }
};
