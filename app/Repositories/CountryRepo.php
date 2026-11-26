<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepo<Country>
 */
class CountryRepo extends BaseRepo
{
    protected function model(): string
    {
        return Country::class;
    }

    /** The country's name (English) for its code, or null when unknown */
    public function nameOf(string $code): ?string
    {
        return $this->query()->where('code', strtoupper($code))->value('name');
    }

    /**
     * Currency code of every country that names one, by country code.
     *
     * @return array<string, string>
     */
    public function currencyCodes(): array
    {
        return $this->query()
            ->whereNotNull('currency_code')
            ->pluck('currency_code', 'code')
            ->map(fn ($c) => (string)$c)
            ->all();
    }

    /** The currency a country names for itself, or null */
    public function currencyCodeFor(string $country): ?string
    {
        $code = $this->query()->where('code', $country)->value('currency_code');

        return $code === null ? null : (string)$code;
    }

    /**
     * The ids of the users who review prices for a country (empty: any admin may).
     *
     * @return list<int>
     */
    public function reviewerIds(string $country): array
    {
        return $this->query()
            ->where('code', $country)
            ->first()
            ?->reviewers()
            ->pluck('users.id')
            ->all() ?? [];
    }

    /**
     * @return Collection<int, Country>
     */
    public function allActive(): Collection
    {
        return $this->query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    public function countryOptions(): array
    {
        return $this->query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (Country $c) => ['code' => $c->code, 'name' => $c->name])
            ->all();
    }
}
