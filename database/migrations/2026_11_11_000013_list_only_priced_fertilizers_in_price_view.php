<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A fertiliser with no active price was still in the app's fertilizer price list,
 * with only the two marker rows ("I don't know" and "Exact price") and no price
 * options. The marker rows are now given only to a fertiliser that has at least
 * one active, national, effective per-bag price, the same test the price options
 * use, so an unpriced fertiliser is not in the list at all until a price is
 * activated. Everything else in the view is unchanged.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS fertilizer_prices');
        DB::statement($this->view(onlyPriced: true));
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS fertilizer_prices');
        DB::statement($this->view(onlyPriced: false));
    }

    private function view(bool $onlyPriced): string
    {
        $priced = $onlyPriced ? <<<'SQL'

WHERE EXISTS (
    SELECT 1 FROM default_prices p
    WHERE CONCAT(p.fertilizer_type, '_', p.country) = f.fertilizer_key
      AND p.unit = 'per_bag'
      AND p.status = 'active'
      AND p.region IS NULL
      AND p.price > 0
      AND (p.effective_from IS NULL OR p.effective_from <= CURDATE())
)
SQL : '';

        return <<<SQL
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
FROM fertilizers f{$priced}
UNION ALL
SELECT 1000000 + f.id * 10 + 5, f.country, f.fertilizer_key, -1, -1, -1, 1, 5, 'Exact price', f.updated_at, f.updated_at
FROM fertilizers f{$priced}
SQL;
    }
};
