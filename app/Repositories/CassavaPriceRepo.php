<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\CassavaPrice;

class CassavaPriceRepo extends BaseRepo
{
    protected function model(): string
    {
        return CassavaPrice::class;
    }
}
