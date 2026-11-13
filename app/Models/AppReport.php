<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\AppReport as BaseAppReport;

class AppReport extends BaseAppReport
{
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
