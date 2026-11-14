<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\User as BaseUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends BaseUser
{
    /** Social sign-ins (Google, ...) linked to this account. */
    public function identities(): HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }

    /** Countries this admin may review prices for (decision D4). */
    public function countriesReviewed(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_reviewers', 'user_id', 'country', 'id', 'code');
    }
}
