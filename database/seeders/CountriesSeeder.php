<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Exception;
use League\Csv\Reader;

class CountriesSeeder extends Seeder
{
    /**
     * @throws Exception
     */
    public function run(): void
    {
        $csvUrl = 'https://raw.githubusercontent.com/google/dspl/master/samples/google/canonical/countries.csv';

        // Download CSV content
        $csvContent = file_get_contents($csvUrl);

        // Parse CSV
        $csv = Reader::fromString($csvContent);

        $csv->setHeaderOffset(0);

        $batch = [];
        $batchSize = 500;

        foreach ($csv as $record) {

            $batch[] = [
                'code' => $record['country'],
                'name' => $record['name'],
                'latitude' => (float)$record['latitude'],
                'longitude' => (float)$record['longitude'],

                // bounding box unknown for now
                'min_latitude' => null,
                'max_latitude' => null,
                'min_longitude' => null,
                'max_longitude' => null,

                // geometry must NOT be null for MariaDB spatial index
                'boundary' => DB::raw("ST_GeomFromText('GEOMETRYCOLLECTION EMPTY')"),

                'active' => false,
                'sort_order' => 9999,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('countries')->upsert(
                    $batch,
                    ['code'], // unique key
                    [
                        'name',
                        'latitude',
                        'longitude',
                        'min_latitude',
                        'max_latitude',
                        'min_longitude',
                        'max_longitude',
                        'boundary',
                        'active',
                        'sort_order',
                        'updated_at',
                    ]
                );
                $batch = [];
            }
        }

        // Insert remaining rows
        if (!empty($batch)) {
            DB::table('countries')->upsert(
                $batch,
                ['code'],
                [
                    'name',
                    'latitude',
                    'longitude',
                    'min_latitude',
                    'max_latitude',
                    'min_longitude',
                    'max_longitude',
                    'boundary',
                    'active',
                    'sort_order',
                    'updated_at',
                ]
            );
        }

        $supportedCountries = [
            ['code' => 'NG', 'name' => 'Nigeria', 'sort_order' => 1],
            ['code' => 'TZ', 'name' => 'Tanzania', 'sort_order' => 2],
            ['code' => 'RW', 'name' => 'Rwanda', 'sort_order' => 3],
            ['code' => 'GH', 'name' => 'Ghana', 'sort_order' => 4],
            ['code' => 'BI', 'name' => 'Burundi', 'sort_order' => 5],
        ];

        foreach ($supportedCountries as $c) {
            DB::table('countries')->where('code', $c['code'])->update([
                'active' => true,
                'sort_order' => $c['sort_order'],
                'updated_at' => now(),
            ]);
        }
    }
}
