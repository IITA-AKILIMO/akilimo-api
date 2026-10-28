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
 * Class AppReport
 *
 * @property string|null $country_code
 * @property Carbon|null $created_at
 * @property string|null $device_token
 * @property bool|null $excluded
 * @property bool|null $fr
 * @property string|null $full_names
 * @property string|null $gender
 * @property bool|null $ic
 * @property int $id
 * @property float|null $lat
 * @property float|null $lon
 * @property string|null $phone_number
 * @property bool|null $pp
 * @property bool|null $sph
 * @property bool|null $spp
 * @property Carbon|null $updated_at
 * @property string|null $user_type
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereExcluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereFr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereFullNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereIc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport wherePp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereSph($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereSpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppReport whereUserType($value)
 *
 * @mixin \Eloquent
 */
class AppReport extends BaseModel
{
    protected $table = 'app_report';

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'excluded' => 'bool',
        'fr' => 'bool',
        'ic' => 'bool',
        'pp' => 'bool',
        'spp' => 'bool',
        'sph' => 'bool',
    ];

    protected $hidden = [
        'device_token',
    ];

    protected $fillable = [
        'device_token',
        'country_code',
        'lat',
        'lon',
        'full_names',
        'phone_number',
        'gender',
        'excluded',
        'user_type',
        'fr',
        'ic',
        'pp',
        'spp',
        'sph',
    ];
}
