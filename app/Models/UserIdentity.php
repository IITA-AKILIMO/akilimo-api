<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A social sign-in linked to a farmer account.
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $subject
 */
class UserIdentity extends Model
{
    protected $fillable = ['user_id', 'provider', 'subject'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
