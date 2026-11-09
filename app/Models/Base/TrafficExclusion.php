<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class TrafficExclusion
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereMatchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrafficExclusion whereValue($value)
 *
 * @mixin \Eloquent
 */
class TrafficExclusion extends BaseModel
{
    protected $table = 'traffic_exclusions';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $fillable = [
        'field',
        'match_type',
        'value',
        'note',
        'active',
    ];
}
