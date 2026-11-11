<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\PriceCatalogue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PriceCatalogueHistory
 *
 * @property string $action
 * @property int|null $changed_by
 * @property string $country
 * @property Carbon|null $created_at
 * @property int|null $price_catalogue_id
 * @property int $id
 * @property string $item
 * @property string|null $new_values
 * @property string|null $note
 * @property string|null $old_values
 * @property string|null $region
 * @property Carbon|null $updated_at
 * @property PriceCatalogue|null $priceCatalogue
 * @property User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory wherePriceCatalogueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceCatalogueHistory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PriceCatalogueHistory extends BaseModel
{
    protected $table = 'price_catalogue_history';

    protected $casts = [
        'price_catalogue_id' => 'int',
        'changed_by' => 'int',
    ];

    protected $fillable = [
        'price_catalogue_id',
        'country',
        'item',
        'region',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'note',
    ];

    public function priceCatalogue(): BelongsTo
    {
        return $this->belongsTo(PriceCatalogue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
