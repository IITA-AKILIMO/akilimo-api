<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ApiKey
 *
 * @property string|null $abilities
 * @property Carbon|null $created_at
 * @property Carbon|null $expires_at
 * @property int $id
 * @property bool $is_active
 * @property string $key_hash
 * @property string $key_prefix
 * @property Carbon|null $last_used_at
 * @property string $name
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereAbilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereKeyHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereKeyPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ApiKey extends BaseModel
{
    protected $table = 'api_keys';

    protected $casts = [
        'user_id' => 'int',
        'is_active' => 'bool',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'key_prefix',
        'key_hash',
        'abilities',
        'is_active',
        'last_used_at',
        'expires_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
