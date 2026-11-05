<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The compute service's planting-practices advice fills "This will {direction} cost by {amount}" with the
 * translations `incr` / `decr` (process-PP.R), which were never added, so that advice failed with
 * "Missing translation key 'decr'". The compute service copies this table (GET /v1/translations), so the
 * keys are added here; the Swahili template reads "Hii ita{direction} gharama", hence the verb stems.
 */
return new class extends Migration {
    private const ROWS = [
        ['key' => 'incr', 'en' => 'increase', 'sw' => 'ongeza'],
        ['key' => 'decr', 'en' => 'decrease', 'sw' => 'punguza'],
    ];

    public function up(): void
    {
        foreach (self::ROWS as $row) {
            DB::table('translations')->updateOrInsert(['key' => $row['key']], [
                'en' => $row['en'],
                'sw' => $row['sw'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('translations')->whereIn('key', array_column(self::ROWS, 'key'))->delete();
    }
};
