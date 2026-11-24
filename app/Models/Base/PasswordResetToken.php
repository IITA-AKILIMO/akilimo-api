<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class PasswordResetToken
 *
 * @property Carbon|null $created_at
 * @property string $email
 * @property string $token
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereToken($value)
 *
 * @mixin \Eloquent
 */
class PasswordResetToken extends BaseModel
{
    protected $table = 'password_reset_tokens';

    protected $primaryKey = 'email';

    public $incrementing = false;

    public $timestamps = false;

    protected $hidden = [
        'token',
    ];

    protected $fillable = [
        'token',
    ];
}
