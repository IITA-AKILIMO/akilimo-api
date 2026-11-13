<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Service\RecommendationReportService;
use Illuminate\Console\Command;

class PruneReports extends Command
{
    protected $signature = 'reports:prune';

    protected $description = 'Delete recommendation PDF reports older than akilimo-compute.reports.ttl_days';

    public function handle(RecommendationReportService $reports): int
    {
        $this->info(sprintf('Removed %d report(s).', $reports->prune()));

        return self::SUCCESS;
    }
}
