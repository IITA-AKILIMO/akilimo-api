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
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends BaseUser
{
    protected $casts = [
        'id' => 'int',
        'email_verified_at' => 'datetime',
        'consent_at' => 'datetime',
        'role' => EnumUserRole::class,
    ];

    /** The farmer-account columns live here, not in the generated Base model, so regenerating it keeps them. */
    public function __construct(array $attributes = [])
    {
        $this->mergeFillable(['consent_at', 'consent_version']);

        parent::__construct($attributes);
    }

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
