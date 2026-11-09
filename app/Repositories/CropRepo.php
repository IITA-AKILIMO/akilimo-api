<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Crop;

class CropRepo extends BaseRepo
{
    protected function model(): string
    {
        return Crop::class;
    }
}
