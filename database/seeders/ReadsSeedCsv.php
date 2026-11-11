<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Generator;
use League\Csv\Reader;

/**
 * Reads a file in database/seeders/data as rows keyed by the header. An empty cell is null, so a nullable
 * column is left empty instead of holding an empty string.
 */
trait ReadsSeedCsv
{
    /**
     * @return Generator<int, array<string, string|null>>
     */
    protected function csvRows(string $file): Generator
    {
        $csv = Reader::from(database_path('seeders/data/' . $file), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            yield array_map(fn ($v) => $v === '' ? null : $v, $record);
        }
    }
}
