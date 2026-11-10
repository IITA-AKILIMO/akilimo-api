<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * price_bands multiplies an item's catalogue price by price_choice_units.quantity
 * (maize_grain: per kg in the catalogue, shown per 50 kg). Items without a row
 * are unchanged, so cassava and sweet potato bands are the same as before.
 *
 * down() restores the definition from migration 000010.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement($this->definition(withUnits: true));
    }

    public function down(): void
    {
        DB::statement($this->definition(withUnits: false));
    }

    private function definition(bool $withUnits): string
    {
        $join = $withUnits ? 'LEFT JOIN price_choice_units u ON u.item = d.item' : '';
        $quantity = $withUnits ? 'COALESCE(u.quantity, 1)' : '1';
        $reference = "(CASE WHEN c.default_price_basis = 'subsidised' AND d.subsidised_price IS NOT NULL THEN d.subsidised_price ELSE d.price END) * {$quantity}";

        return <<<SQL
CREATE OR REPLACE VIEW price_bands AS
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
               {$reference} * (1 + (s.change_pct - s.band_half_pct) / 100) / COALESCE(r.step, 1)
           ) * COALESCE(r.step, 1) AS DECIMAL(10, 2)) AS lo,
           CAST(ROUND(
               {$reference} * (1 + (s.change_pct + s.band_half_pct) / 100) / COALESCE(r.step, 1)
           ) * COALESCE(r.step, 1) AS DECIMAL(10, 2)) AS hi
    FROM default_prices d
    JOIN countries c ON c.code = d.country
    CROSS JOIN price_choice_steps s
    LEFT JOIN price_choice_rounding r ON r.currency = d.currency
    {$join}
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
SQL;
    }
};
