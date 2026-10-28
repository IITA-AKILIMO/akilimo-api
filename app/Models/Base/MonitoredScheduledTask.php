<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\MonitoredScheduledTaskLogItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class MonitoredScheduledTask
 *
 * @property Carbon|null $created_at
 * @property string $cron_expression
 * @property int $grace_time_in_minutes
 * @property int $id
 * @property Carbon|null $last_failed_at
 * @property Carbon|null $last_finished_at
 * @property Carbon|null $last_pinged_at
 * @property Carbon|null $last_skipped_at
 * @property Carbon|null $last_started_at
 * @property string $name
 * @property string|null $ping_url
 * @property Carbon|null $registered_on_oh_dear_at
 * @property string|null $timezone
 * @property string|null $type
 * @property Carbon|null $updated_at
 * @property Collection|MonitoredScheduledTaskLogItem[] $monitoredScheduledTaskLogItems
 * @property-read int|null $monitored_scheduled_task_log_items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereCronExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereGraceTimeInMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereLastFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereLastFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereLastPingedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereLastSkippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereLastStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask wherePingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereRegisteredOnOhDearAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTask whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MonitoredScheduledTask extends BaseModel
{
    protected $table = 'monitored_scheduled_tasks';

    protected $casts = [
        'last_started_at' => 'datetime',
        'last_finished_at' => 'datetime',
        'last_failed_at' => 'datetime',
        'last_skipped_at' => 'datetime',
        'registered_on_oh_dear_at' => 'datetime',
        'last_pinged_at' => 'datetime',
        'grace_time_in_minutes' => 'int',
    ];

    protected $fillable = [
        'name',
        'type',
        'cron_expression',
        'timezone',
        'ping_url',
        'last_started_at',
        'last_finished_at',
        'last_failed_at',
        'last_skipped_at',
        'registered_on_oh_dear_at',
        'last_pinged_at',
        'grace_time_in_minutes',
    ];

    public function monitoredScheduledTaskLogItems(): HasMany
    {
        return $this->hasMany(MonitoredScheduledTaskLogItem::class);
    }
}
