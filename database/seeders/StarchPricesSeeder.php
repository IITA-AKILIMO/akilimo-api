<?php

namespace Database\Seeders;

use App\Models\StarchFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class StarchPricesSeeder extends Seeder
{
    /**
     * @throws Exception
     * @throws UnavailableStream
     */
    public function run(): void
    {
        // Use fromPath instead of createFromPath
        $csv = Reader::from(database_path('seeders/data/starch_prices.csv'), 'r');
        $csv->setHeaderOffset(0);

        $batch = [];
        $batchSize = 1000;

        foreach ($csv as $record) {
            $factory = StarchFactory::firstOrCreate(
                ['factory_name' => $record['starchFactory']],
                [
                    'factory_label' => $record['starchFactory_label'] ?? '',
                    'country' => $record['country'] ?? '',
                    // Only applies when the factory is created; an admin's later toggle is kept
                    'factory_active' => ($record['active'] ?? '1') !== '0',
                ]
            );

            $batch[] = [
                'starch_factory_id' => $factory->id,
                'price_class' => (int) $record['class'],
                'min_starch' => (float) $record['minStarch'],
                'range_starch' => $record['rangeStarch'] ?? '',
                'price' => (float) $record['price'],
                'currency' => ($record['currency'] ?? '') ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('starch_prices')->upsert(
                    $batch,
                    ['starch_factory_id', 'price_class'],
                    ['min_starch', 'range_starch', 'price', 'updated_at']
                );
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('starch_prices')->upsert(
                $batch,
                ['starch_factory_id', 'price_class'],
                ['min_starch', 'range_starch', 'price', 'updated_at']
            );
        }
    }
}
