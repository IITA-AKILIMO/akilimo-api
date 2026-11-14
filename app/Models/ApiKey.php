<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Auth\TokenAbility;
use App\Models\Base\ApiKey as BaseApiKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends BaseApiKey
{

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function can(string $ability): bool
    {
        return TokenAbility::check($this->abilities ?? ['*'], $ability);
    }
}
