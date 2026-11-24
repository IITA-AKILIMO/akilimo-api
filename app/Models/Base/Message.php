<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use App\Enums\EnumMessageType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Message
 *
 * @property string $body
 * @property string|null $country
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $expires_at
 * @property string $id
 * @property string|null $language
 * @property Carbon|null $pushed_at
 * @property string|null $target
 * @property string $title
 * @property EnumMessageType $type
 * @property Carbon|null $updated_at
 * @property User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message wherePushedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Message extends BaseModel
{
    protected $table = 'messages';

    public $incrementing = false;

    protected $casts = [
        'id' => 'string',
        'type' => EnumMessageType::class,
        'expires_at' => 'datetime',
        'pushed_at' => 'datetime',
        'created_by' => 'int',
    ];

    protected $fillable = [
        'type',
        'country',
        'language',
        'title',
        'body',
        'target',
        'expires_at',
        'pushed_at',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
