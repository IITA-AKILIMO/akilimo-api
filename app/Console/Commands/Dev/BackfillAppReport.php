<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands\Dev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAppReport extends Command
{
    protected $signature = 'app-report:backfill
                            {--limit=0 : Max rows to process (0 = all)}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Backfill app_report from api_requests for rows not yet present (idempotent)';

    public function handle(): int
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->error('This command requires MariaDB/MySQL — SQLite does not support the JSON functions used here.');

            return self::FAILURE;
        }

        $limit = (int)$this->option('limit');
        $limitClause = $limit > 0 ? "LIMIT {$limit}" : '';

        $pendingQuery = "SELECT COUNT(*) AS cnt FROM (
            SELECT ar.id
            FROM api_requests ar
            LEFT JOIN app_report rep ON rep.id = ar.id
            WHERE rep.id IS NULL
            {$limitClause}
        ) sub";
        $pending = DB::selectOne($pendingQuery)?->cnt ?? 0;

        if ($pending === 0) {
            $this->info('app_report is already up to date — nothing to backfill.');

            return self::SUCCESS;
        }

        $this->line("Rows to backfill: <comment>{$pending}</comment>");

        if (!$this->option('force') && !$this->confirm('Proceed?', true)) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        $this->line('Backfilling…');

        $bar = $this->output->createProgressBar(1);
        $bar->start();

        // excluded is computed inline (same logic as exclusion_flag_evaluation proc)
        // so that any BEFORE INSERT trigger on app_report finds the column already
        // populated and does not need to call the procedure.
        DB::unprepared("
            INSERT IGNORE INTO app_report
                (id, device_token, country_code, lat, lon, full_names, phone_number,
                 gender, user_type, fr, ic, pp, sph, spp, excluded, created_at, updated_at)
            SELECT
                ar.id,
                ar.device_token,
                REPLACE(JSON_EXTRACT(ar.plumber_request, '$.country'),               '\"', ''),
                REPLACE(JSON_EXTRACT(ar.plumber_request, '$.lat'),                   '\"', ''),
                REPLACE(JSON_EXTRACT(ar.plumber_request, '$.lon'),                   '\"', ''),
                REPLACE(JSON_EXTRACT(ar.droid_request,   '$.userInfo.userName'),     '\"', ''),
                REPLACE(JSON_EXTRACT(ar.droid_request,   '$.userInfo.mobileNumber'), '\"', ''),
                REPLACE(JSON_EXTRACT(ar.droid_request,   '$.userInfo.gender'),       '\"', ''),
                'NA',
                IF(STRCMP(REPLACE(JSON_EXTRACT(ar.plumber_request, '$.FR'),  '\"', ''), 'false') = 1, 1, 0),
                IF(STRCMP(REPLACE(JSON_EXTRACT(ar.plumber_request, '$.IC'),  '\"', ''), 'false') = 1, 1, 0),
                IF(STRCMP(REPLACE(JSON_EXTRACT(ar.plumber_request, '$.PP'),  '\"', ''), 'false') = 1, 1, 0),
                IF(STRCMP(REPLACE(JSON_EXTRACT(ar.plumber_request, '$.SPH'), '\"', ''), 'false') = 1, 1, 0),
                IF(STRCMP(REPLACE(JSON_EXTRACT(ar.plumber_request, '$.SPP'), '\"', ''), 'false') = 1, 1, 0),
                -- Staff/test flag, set from the admin-managed rules (TrafficExclusionService)
                ar.excluded,
                ar.created_at,
                ar.updated_at
            FROM api_requests ar
            LEFT JOIN app_report rep ON rep.id = ar.id
            WHERE rep.id IS NULL
            ORDER BY ar.created_at DESC
            {$limitClause}
        ");

        $bar->finish();
        $this->newLine();

        $inserted = DB::table('app_report')->count();
        $this->info("Done. app_report now contains {$inserted} rows.");

        return self::SUCCESS;
    }
}
