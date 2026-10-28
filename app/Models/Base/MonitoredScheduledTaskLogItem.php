<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\MonitoredScheduledTask;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MonitoredScheduledTaskLogItem
 *
 * @property Carbon|null $created_at
 * @property int $id
 * @property string|null $meta
 * @property int $monitored_scheduled_task_id
 * @property string $type
 * @property Carbon|null $updated_at
 * @property MonitoredScheduledTask $monitoredScheduledTask
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem whereMonitoredScheduledTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonitoredScheduledTaskLogItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MonitoredScheduledTaskLogItem extends BaseModel
{
    protected $table = 'monitored_scheduled_task_log_items';

    protected $casts = [
        'monitored_scheduled_task_id' => 'int',
    ];

    protected $fillable = [
        'monitored_scheduled_task_id',
        'type',
        'meta',
    ];

    public function monitoredScheduledTask(): BelongsTo
    {
        return $this->belongsTo(MonitoredScheduledTask::class);
    }
}
