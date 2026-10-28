<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;

/**
 * Class RequestFertilizer
 *
 * @property bool|null $available
 * @property int $fertilizer_id
 * @property string $fertilizer_type
 * @property float $price
 * @property int|null $request_id
 * @property float $weight
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer whereFertilizerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer whereFertilizerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestFertilizer whereWeight($value)
 *
 * @mixin \Eloquent
 */
class RequestFertilizer extends BaseModel
{
    protected $table = 'request_fertilizer';

    protected $primaryKey = 'fertilizer_id';

    public $timestamps = false;

    protected $casts = [
        'request_id' => 'int',
        'available' => 'bool',
        'price' => 'float',
        'weight' => 'float',
    ];

    protected $fillable = [
        'request_id',
        'fertilizer_type',
        'available',
        'price',
        'weight',
    ];
}
