<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The units cassava is sold in (cassava_units.csv), with the ids the app already holds. Adds what is
 * missing and never changes a row that exists.
 */
class CassavaUnitsSeeder extends Seeder
{
    use ReadsSeedCsv;

    public function run(): void
    {
        foreach ($this->csvRows('cassava_units.csv') as $row) {
            DB::table('cassava_units')->insertOrIgnore([
                'id' => (int) $row['id'],
                'unit_weight' => (int) $row['unit_weight'],
                'label' => $row['label'],
                'sort_order' => (int) $row['sort_order'],
                'description' => $row['description'],
                'is_active' => (bool) $row['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
