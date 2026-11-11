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
use League\Csv\UnavailableStream;

class DefaultPricesSeeder extends Seeder
{
    /** Burundi FOMI is sold in real 25 kg bags, not 50 kg. */
    private const BURUNDI_25KG = ['FOMIIMBURA', 'FOMIBAGARA', 'FOMITOTAHAZA'];

    /**
     * @throws Exception
     * @throws UnavailableStream
     */
    public function run(): void
    {
        // Use fromPath instead of createFromPath
        $csv = Reader::from(database_path('seeders/data/default_prices.csv'), 'r');
        $csv->setHeaderOffset(0);

        $units = DB::table('price_items')->pluck('unit', 'item')->all();

        $batch = [];
        $batchSize = 1000;

        foreach ($csv as $record) {
            $country = $record['Country'];
            $item = $record['Item'];
            // The unit the item is priced in (price_items); anything unlisted is fertiliser, per bag
            $unit = $units[$item] ?? 'per_bag';
            $bagKg = $unit === 'per_bag'
                ? ($country === 'BI' && in_array($item, self::BURUNDI_25KG, true) ? 25 : config('price_catalogue.default_bag_kg'))
                : null;

            $batch[] = [
                'country' => $country,
                'item' => $item,
                'price' => $record['Price'],
                'unit' => $unit,
                'bag_kg' => $bagKg,
                'currency' => $record['currency'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('price_catalogue')->upsert(
                    $batch,
                    ['country', 'item'],
                    ['currency', 'price', 'unit', 'bag_kg', 'updated_at']
                );
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('price_catalogue')->upsert(
                $batch,
                ['country', 'item'],
                ['currency', 'price', 'unit', 'bag_kg', 'updated_at']
            );

        }
    }
}
