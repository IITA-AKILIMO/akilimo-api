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
 * The crops the app offers (crops.csv). Adds what is missing and never changes a row that exists.
 */
class CropsSeeder extends Seeder
{
    use ReadsSeedCsv;

    public function run(): void
    {
        foreach ($this->csvRows('crops.csv') as $row) {
            DB::table('crops')->insertOrIgnore([
                'id' => (int) $row['id'],
                'crop_key' => $row['crop_key'],
                'name' => $row['name'],
                'description' => $row['description'],
                'active' => (bool) $row['active'],
                'advice_available' => (bool) $row['advice_available'],
                'countries' => $row['countries'],
                'sort_order' => (int) $row['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
