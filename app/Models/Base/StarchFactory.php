<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\StarchPrice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class StarchFactory
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property bool|null $factory_active
 * @property string $factory_label
 * @property string $factory_name
 * @property int $id
 * @property int|null $sort_order
 * @property Carbon|null $updated_at
 * @property Collection|StarchPrice[] $starchPrices
 * @property-read int|null $starch_prices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereFactoryActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereFactoryLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereFactoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarchFactory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StarchFactory extends BaseModel
{
    protected $table = 'starch_factories';

    protected $casts = [
        'factory_active' => 'bool',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'factory_name',
        'factory_label',
        'country',
        'factory_active',
        'sort_order',
    ];

    public function starchPrices(): HasMany
    {
        return $this->hasMany(StarchPrice::class);
    }
}
