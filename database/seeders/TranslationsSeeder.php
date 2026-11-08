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

class TranslationsSeeder extends Seeder
{
    /**
     * @throws Exception
     * @throws UnavailableStream
     */
    public function run(): void
    {
        // Use fromPath instead of createFromPath
        $csv = Reader::from(database_path('seeders/data/translations.csv'), 'r');
        $csv->setHeaderOffset(0);

        $batch = [];
        $batchSize = 1000;

        foreach ($csv as $record) {
            $batch[] = [
                'key' => $record['key'],
                'en' => $record['en'],
                'sw' => $record['sw'] ?? null,
                'rw' => $record['rw'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('translations')->upsert(
                    $batch,
                    ['key'],
                    ['en', 'sw', 'rw', 'updated_at']
                );
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('translations')->upsert(
                $batch,
                ['key'],
                ['en', 'sw', 'rw', 'updated_at']
            );

        }
    }
}
