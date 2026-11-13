<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\TrafficExclusion;

class TrafficExclusionRepo extends BaseRepo
{
    protected function model(): string
    {
        return TrafficExclusion::class;
    }
}
