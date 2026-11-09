<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Enums\EnumUserRole;
use App\Models\Base\User as BaseUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends BaseUser
{
    protected $casts = [
        'id' => 'int',
        'email_verified_at' => 'datetime',
        'role' => EnumUserRole::class,
    ];

    /** Countries this admin may review prices for (decision D4). */
    public function countriesReviewed(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_reviewers', 'user_id', 'country', 'id', 'code');
    }
}
