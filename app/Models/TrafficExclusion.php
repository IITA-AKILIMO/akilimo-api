<?php

namespace App\Models;

use App\Models\Base\TrafficExclusion as BaseTrafficExclusion;
use Illuminate\Database\Eloquent\Builder;

/**
 * A rule that marks requests as staff or test traffic.
 *
 * Hand-written behaviour lives here, not in Base\TrafficExclusion, which the model generator overwrites.
 */
class TrafficExclusion extends BaseTrafficExclusion
{
    /** Rules that are switched on */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
