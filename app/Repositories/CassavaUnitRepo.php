<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\CassavaUnit;

class CassavaUnitRepo extends BaseRepo
{
    /**
     * @return class-string<CassavaUnit>
     */
    protected function model(): string
    {
        return CassavaUnit::class;
    }
}
