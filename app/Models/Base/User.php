<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models\Base;

use App\Core\Models\BaseUser;
use App\Enums\EnumUserRole;
use App\Models\ApiKey;
use App\Models\ApiRequest;
use App\Models\CountryReviewer;
use App\Models\PriceCatalogue;
use App\Models\PriceCatalogueHistory;
use App\Models\UserIdentity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Class User
 *
 * @property Carbon|null $consent_at
 * @property string|null $consent_version
 * @property Carbon|null $created_at
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property int $id
 * @property string $name
 * @property string|null $password
 * @property string|null $remember_token
 * @property EnumUserRole $role
 * @property Carbon|null $updated_at
 * @property string $username
 * @property Collection|ApiKey[] $apiKeys
 * @property Collection|ApiRequest[] $apiRequests
 * @property Collection|CountryReviewer[] $countryReviewers
 * @property Collection|PriceCatalogueHistory[] $priceCatalogueHistories
 * @property Collection|PriceCatalogue[] $priceCatalogues
 * @property Collection|UserIdentity[] $userIdentities
 * @property-read int|null $api_keys_count
 * @property-read int|null $api_requests_count
 * @property-read int|null $country_reviewers_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $price_catalogue_histories_count
 * @property-read int|null $price_catalogues_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read int|null $user_identities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereConsentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereConsentVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 *
 * @mixin \Eloquent
 */
class User extends BaseUser
{
    protected $table = 'users';

    protected $casts = [
        'role' => EnumUserRole::class,
        'email_verified_at' => 'datetime',
        'consent_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'email_verified_at',
        'consent_at',
        'consent_version',
        'password',
        'remember_token',
    ];

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function apiRequests(): HasMany
    {
        return $this->hasMany(ApiRequest::class);
    }

    public function countryReviewers(): HasMany
    {
        return $this->hasMany(CountryReviewer::class);
    }

    public function priceCatalogueHistories(): HasMany
    {
        return $this->hasMany(PriceCatalogueHistory::class, 'changed_by');
    }

    public function priceCatalogues(): HasMany
    {
        return $this->hasMany(PriceCatalogue::class, 'updated_by');
    }

    public function userIdentities(): HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }
}
