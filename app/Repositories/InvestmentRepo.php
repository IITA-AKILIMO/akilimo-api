<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\InvestmentAmount;

class InvestmentRepo extends BaseRepo
{
    protected function model(): string
    {
        return InvestmentAmount::class;
    }
}
