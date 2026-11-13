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
 * The fertilizers: each one once (fertilizer_types.csv) and where each is sold and how it is used
 * (country_fertilizers.csv). The country rows keep their ids, because the app and the price option lists
 * refer to them. Every fertilizer's catalogue label is also a price item, so it can be priced.
 *
 * Adds what is missing and never changes a row that exists, so it is safe to run again.
 */
class FertilizerSeeder extends Seeder
{
    use ReadsSeedCsv;

    public function run(): void
    {
        foreach ($this->csvRows('fertilizer_types.csv') as $row) {
            DB::table('fertilizer_types')->insertOrIgnore([
                'type' => $row['type'],
                'label' => $row['label'],
                'name' => $row['name'],
                'weight' => (int) $row['weight'],
                'category' => $row['category'] ?? 'OTHER',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // A catalogue price names its item, and a fertilizer's item is its label (per bag)
            if ($row['label'] !== null) {
                if (! DB::table('price_items')->where('item', $row['label'])->exists()) {
                    DB::table('price_items')->insert(['item' => $row['label'], 'unit' => 'per_bag', 'category' => 'FERTILIZER']);
                } else {
                    // already there from a migration, before there was a fertilizer to say what it is
                    DB::table('price_items')->where('item', $row['label'])->where('category', 'OTHER')->update(['category' => 'FERTILIZER']);
                }
            }
        }

        foreach ($this->csvRows('country_fertilizers.csv') as $row) {
            DB::table('country_fertilizers')->insertOrIgnore([
                'id' => (int) $row['id'],
                'fertilizer_type' => $row['fertilizer_type'],
                'country' => $row['country'],
                'use_case' => $row['use_case'],
                'cis' => (bool) $row['cis'],
                'cim' => (bool) $row['cim'],
                'available' => (bool) $row['available'],
                'sort_order' => (int) $row['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
