<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Service\CoverageService;
use Illuminate\Console\Command;

class SyncCoverage extends Command
{
    protected $signature = 'coverage:sync
                            {country?* : ISO country codes to sync (default: all supported countries)}';

    protected $description = 'Copy the compute service\'s supported area (GeoJSON) per country into the coverage_areas table';

    public function handle(CoverageService $coverage): int
    {
        $results = $coverage->sync($this->argument('country') ?: null);

        $this->table(
            ['Country', 'Result', 'Detail'],
            collect($results)->map(fn ($r, $country) => [$country, $r['status'], $r['message'] ?? ''])->values(),
        );

        $failed = collect($results)->where('status', 'failed')->count();

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
