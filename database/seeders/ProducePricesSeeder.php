<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProducePricesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('produce_prices')->truncate();

        $tables = [
            'maize_prices' => 'maize',
            'potato_prices' => 'potato',
            'cassava_prices' => 'cassava',
        ];

        foreach ($tables as $table => $produce) {
            $columns = DB::getSchemaBuilder()->getColumnListing($table);

            $rows = DB::table($table)
                ->select(...array_intersect(
                    $columns,
                    [
                        'country',
                        'min_local_price',
                        'max_local_price',
                        'min_price',
                        'max_price',
                        'price_active',
                        'sort_order',
                        'created_at',
                    ]
                ))
                ->get()
                ->map(function ($r) use ($produce) {
                    return [
                        'country' => $r->country ?? 'NG',
                        'produce_name' => $produce,
                        'min_price' => $r->min_local_price ?? 0,
                        'max_price' => $r->max_local_price ?? 0,
                        'is_min_price' => (bool)($r->min_price ?? false),
                        'is_max_price' => (bool)($r->max_price ?? false),
                        'is_active' => (bool)($r->price_active ?? true),
                        'sort_order' => $r->sort_order ?? 0,
                        'created_at' => $r->created_at ?? now(),
                        'updated_at' => now(),
                    ];
                })
                ->toArray();

            if (!empty($rows)) {
                DB::table('produce_prices')->insert($rows);
            }
        }
    }
}
