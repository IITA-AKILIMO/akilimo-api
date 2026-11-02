<?php

namespace App\Repositories;

use App\Models\TrafficExclusion;

class TrafficExclusionRepo extends BaseRepo
{
    protected function model(): string
    {
        return TrafficExclusion::class;
    }
}
