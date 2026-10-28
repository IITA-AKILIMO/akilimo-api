<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class UserFeedback
 *
 * @property int $akilimo_rec_rating
 * @property string $akilimo_usage
 * @property int $akilimo_useful_rating
 * @property Carbon|null $created_at
 * @property string|null $device_token
 * @property int $id
 * @property string|null $language
 * @property Carbon|null $updated_at
 * @property string|null $use_case
 * @property string $user_type
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereAkilimoRecRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereAkilimoUsage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereAkilimoUsefulRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereUseCase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFeedback whereUserType($value)
 *
 * @mixin \Eloquent
 */
class UserFeedback extends BaseModel
{
    protected $table = 'user_feedback';

    protected $casts = [
        'akilimo_rec_rating' => 'int',
        'akilimo_useful_rating' => 'int',
    ];

    protected $hidden = [
        'device_token',
    ];

    protected $fillable = [
        'akilimo_usage',
        'use_case',
        'user_type',
        'akilimo_rec_rating',
        'akilimo_useful_rating',
        'language',
        'device_token',
    ];
}
