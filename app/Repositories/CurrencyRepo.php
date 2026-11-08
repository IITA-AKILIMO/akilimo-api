<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Currency;

class CurrencyRepo extends BaseRepo
{
    protected function model(): string
    {
        return Currency::class;
    }
}
