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
 * Class LegacyTableArchive
 *
 * @property Carbon $archived_at
 * @property string $definition
 * @property int $row_count
 * @property string $row_data
 * @property string $table_name
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive whereDefinition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive whereRowCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive whereRowData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegacyTableArchive whereTableName($value)
 *
 * @mixin \Eloquent
 */
class LegacyTableArchive extends BaseModel
{
    protected $table = 'legacy_table_archive';

    protected $primaryKey = 'table_name';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'row_count' => 'int',
        'archived_at' => 'datetime',
    ];

    protected $fillable = [
        'definition',
        'row_count',
        'row_data',
        'archived_at',
    ];
}
