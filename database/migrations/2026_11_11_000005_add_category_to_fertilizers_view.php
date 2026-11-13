<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The fertilizers view also shows the fertiliser's category, so the API and the
 * admin list can show and filter by it. Every other column is unchanged.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS fertilizers');
        DB::statement($this->view(withCategory: true));
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS fertilizers');
        DB::statement($this->view(withCategory: false));
    }

    private function view(bool $withCategory): string
    {
        $category = $withCategory ? "       t.category AS category,\n" : '';

        return <<<SQL
CREATE VIEW fertilizers AS
SELECT cf.id AS id,
       t.label AS fertilizer_label,
       t.name AS name,
       t.type AS type,
{$category}       CONCAT(t.type, '_', cf.country) AS fertilizer_key,
       t.weight AS weight,
       cf.country AS country,
       cf.sort_order AS sort_order,
       cf.use_case AS use_case,
       cf.cis AS cis,
       cf.cim AS cim,
       cf.available AS available,
       cf.created_at AS created_at,
       cf.updated_at AS updated_at
FROM country_fertilizers cf
JOIN fertilizer_types t ON t.type = cf.fertilizer_type
SQL;
    }
};
