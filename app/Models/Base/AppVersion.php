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
 * Class AppVersion
 *
 * @property Carbon|null $created_at
 * @property int $id
 * @property int $latest
 * @property string|null $message
 * @property int $min_supported
 * @property string $platform
 * @property string|null $store_url
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereLatest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereMinSupported($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereStoreUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppVersion whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AppVersion extends BaseModel
{
    protected $table = 'app_versions';

    protected $casts = [
        'min_supported' => 'int',
        'latest' => 'int',
    ];

    protected $fillable = [
        'platform',
        'min_supported',
        'latest',
        'message',
        'store_url',
    ];
}
