<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\AppVersion;

/**
 * @extends BaseRepo<AppVersion>
 */
class AppVersionRepo extends BaseRepo
{
    protected function model(): string
    {
        return AppVersion::class;
    }

    public function forPlatform(string $platform): ?AppVersion
    {
        return $this->query()->where('platform', $platform)->first();
    }
}
