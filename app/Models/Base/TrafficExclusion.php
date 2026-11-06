<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TrafficExclusion — a rule that marks requests as staff or test traffic.
 *
 * @property bool $active
 * @property Carbon|null $created_at
 * @property string $field
 * @property int $id
 * @property string $match_type
 * @property string|null $note
 * @property Carbon|null $updated_at
 * @property string $value
 *
 * @method static Builder<static>|TrafficExclusion newModelQuery()
 * @method static Builder<static>|TrafficExclusion newQuery()
 * @method static Builder<static>|TrafficExclusion query()
 *
 * @mixin \Eloquent
 */
class TrafficExclusion extends BaseModel
{
    protected $table = 'traffic_exclusions';

    protected $fillable = [
        'field',
        'match_type',
        'value',
        'note',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /** Rules that are switched on */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
