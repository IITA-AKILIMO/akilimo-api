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
 * Class PushDevice
 *
 * @property string|null $app_version
 * @property string|null $country
 * @property Carbon|null $created_at
 * @property string $device_token
 * @property string $fcm_token
 * @property int $id
 * @property string|null $language
 * @property Carbon|null $last_seen_at
 * @property string $platform
 * @property Carbon|null $updated_at
 * @property int|null $user_id
 * @property User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereFcmToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PushDevice whereUserId($value)
 *
 * @mixin \Eloquent
 */
class PushDevice extends BaseModel
{
    protected $table = 'push_devices';

    protected $casts = [
        'user_id' => 'int',
        'last_seen_at' => 'datetime',
    ];

    protected $hidden = [
        'device_token',
        'fcm_token',
    ];

    protected $fillable = [
        'device_token',
        'fcm_token',
        'platform',
        'app_version',
        'country',
        'language',
        'user_id',
        'last_seen_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
