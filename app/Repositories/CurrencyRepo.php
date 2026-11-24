<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Support\Collection;

class CurrencyRepo extends BaseRepo
{
    protected function model(): string
    {
        return Currency::class;
    }

    /**
     * One row per currency code, by code.
     *
     * @return Collection<int, Currency>
     */
    public function distinctByCode(): Collection
    {
        return $this->query()
            ->whereNotNull('currency_code')
            ->orderBy('currency_code')
            ->get(['currency_code', 'currency_name'])
            ->unique('currency_code');
    }

    /**
     * The first currency row of each country that has one, oldest first.
     *
     * @return Collection<int, Currency>
     */
    public function linkedToCountries(): Collection
    {
        return $this->query()
            ->whereNotNull('country_code')
            ->whereNotNull('currency_code')
            ->orderBy('id')
            ->get(['country_code', 'currency_code'])
            ->unique('country_code');
    }

    /** The currency code linked to a country through a currency row, or null */
    public function codeForCountry(string $country): ?string
    {
        $code = $this->query()->where('country_code', $country)->value('currency_code');

        return $code === null ? null : (string)$code;
    }
}
