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
 * Class ApiRequest
 *
 * @property string|null $country_code
 * @property Carbon|null $created_at
 * @property string|null $device_token
 * @property array $droid_request
 * @property bool|null $excluded
 * @property bool|null $fr
 * @property string|null $full_names
 * @property string|null $gender
 * @property string|null $gender_code
 * @property bool|null $ic
 * @property int $id
 * @property float|null $lat
 * @property float|null $lon
 * @property string|null $phone_number
 * @property array $plumber_request
 * @property array $plumber_response
 * @property bool|null $pp
 * @property int|null $request_duration_ms
 * @property string $request_id
 * @property Carbon|null $request_started_at
 * @property bool|null $sph
 * @property bool|null $spp
 * @property Carbon|null $updated_at
 * @property string|null $use_case
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereDroidRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereExcluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereFr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereFullNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereGenderCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereIc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest wherePlumberRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest wherePlumberResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest wherePp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereRequestDurationMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereRequestStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereSph($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereSpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiRequest whereUseCase($value)
 *
 * @mixin \Eloquent
 */
class ApiRequest extends BaseModel
{
    protected $table = 'api_requests';

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'fr' => 'bool',
        'ic' => 'bool',
        'pp' => 'bool',
        'sph' => 'bool',
        'spp' => 'bool',
        'excluded' => 'bool',
        'droid_request' => 'json',
        'plumber_request' => 'json',
        'request_started_at' => 'datetime',
        'request_duration_ms' => 'int',
        'plumber_response' => 'json',
    ];

    protected $hidden = [
        'device_token',
    ];

    protected $fillable = [
        'request_id',
        'device_token',
        'country_code',
        'lat',
        'lon',
        'full_names',
        'phone_number',
        'gender',
        'fr',
        'ic',
        'pp',
        'sph',
        'spp',
        'excluded',
        'use_case',
        'gender_code',
        'droid_request',
        'plumber_request',
        'request_started_at',
        'request_duration_ms',
        'plumber_response',
    ];
}
