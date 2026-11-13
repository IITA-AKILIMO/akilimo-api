<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\FertilizerType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FertilizerCategory
 *
 * @property string $label
 * @property int $sort_order
 * @property string $value
 * @property Collection|FertilizerType[] $fertilizerTypes
 * @property-read int|null $fertilizer_types_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerCategory whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FertilizerCategory whereValue($value)
 *
 * @mixin \Eloquent
 */
class FertilizerCategory extends BaseModel
{
    protected $table = 'fertilizer_categories';

    protected $primaryKey = 'value';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'label',
        'sort_order',
    ];

    public function fertilizerTypes(): HasMany
    {
        return $this->hasMany(FertilizerType::class, 'category');
    }
}
