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
 * The rules that mark staff and test traffic in the request log (traffic_exclusions.csv). A rule that
 * exists, with the same field, match type and value, is left as it is.
 */
class TrafficExclusionSeeder extends Seeder
{
    use ReadsSeedCsv;

    public function run(): void
    {
        foreach ($this->csvRows('traffic_exclusions.csv') as $row) {
            $key = ['field' => $row['field'], 'match_type' => $row['match_type'], 'value' => $row['value']];

            if (DB::table('traffic_exclusions')->where($key)->exists()) {
                continue;
            }

            DB::table('traffic_exclusions')->insert($key + [
                'note' => $row['note'],
                'active' => (bool) $row['active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
