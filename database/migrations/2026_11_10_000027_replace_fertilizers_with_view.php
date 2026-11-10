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
 * fertilizers becomes a view over fertilizer_types and country_fertilizers, with
 * the same name and columns as before, so the API, the recommendation service
 * and the price lists that read it are unchanged. fertilizer_key is computed
 * (type, underscore, country). Writes go to the two tables through
 * FertilizerRepo.
 *
 * The old table is kept as fertilizers_legacy until a later migration drops it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::rename('fertilizers', 'fertilizers_legacy');

        DB::statement(<<<'SQL'
CREATE VIEW fertilizers AS
SELECT cf.id AS id,
       t.label AS fertilizer_label,
       t.name AS name,
       t.type AS type,
       CONCAT(t.type, '_', cf.country) AS fertilizer_key,
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
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS fertilizers');
        Schema::rename('fertilizers_legacy', 'fertilizers');
    }
};
