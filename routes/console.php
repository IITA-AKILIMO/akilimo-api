<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh the supported area copied from the compute service (the yield grids rarely change)
Schedule::command('coverage:sync')->dailyAt('03:30')->withoutOverlapping()->onOneServer();

// Recommendation PDF reports hold personal details: keep them only as long as their download links last
Schedule::command('reports:prune')->dailyAt('03:45')->withoutOverlapping()->onOneServer();
