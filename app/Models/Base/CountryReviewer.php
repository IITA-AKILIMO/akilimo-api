<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CountryReviewer
 *
 * @property string $country
 * @property Carbon|null $created_at
 * @property int $id
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryReviewer whereUserId($value)
 *
 * @mixin \Eloquent
 */
class CountryReviewer extends BaseModel
{
    protected $table = 'country_reviewers';

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $fillable = [
        'country',
        'user_id',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country', 'code');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
