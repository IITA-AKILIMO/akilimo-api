<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Auth\TokenAbility;
use App\Models\Base\PersonalAccessToken as BasePersonalAccessToken;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalAccessToken extends BasePersonalAccessToken
{
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    public function can(string $ability): bool
    {
        return TokenAbility::check($this->abilities ?? ['*'], $ability);
    }
}
