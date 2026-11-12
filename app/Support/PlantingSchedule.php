<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * How far apart the planting and harvest dates may be. The recommendation engine
 * refuses a schedule outside this window ("the time from planting to harvest must
 * be between 235 and 452 days"), so the app limits its date pickers to it and the
 * API says so before it calls the engine.
 */
final class PlantingSchedule
{
    public const MIN_DAYS = 235;

    public const MAX_DAYS = 452;

    /** Whole days from planting to harvest; negative when the harvest comes first. */
    public static function days(CarbonInterface $planting, CarbonInterface $harvest): int
    {
        return (int)$planting->copy()->startOfDay()->diffInDays($harvest->copy()->startOfDay(), false);
    }

    public static function isValid(CarbonInterface $planting, CarbonInterface $harvest): bool
    {
        $days = self::days($planting, $harvest);

        return $days >= self::MIN_DAYS && $days <= self::MAX_DAYS;
    }

    public static function message(): string
    {
        return sprintf('The time from planting to harvest must be between %d and %d days.', self::MIN_DAYS, self::MAX_DAYS);
    }
}
