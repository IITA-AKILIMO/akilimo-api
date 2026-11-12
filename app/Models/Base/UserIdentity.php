<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserIdentity
 *
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $provider
 * @property string $subject
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserIdentity whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserIdentity extends BaseModel
{
    protected $table = 'user_identities';

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'provider',
        'subject',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
