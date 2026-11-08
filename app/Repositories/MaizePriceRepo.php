<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Data\MinMaxPriceDto;
use App\Models\MaizePrice;

class MaizePriceRepo extends BaseRepo
{
    protected function model(): string
    {
        return MaizePrice::class;
    }

    public function findPriceBandsByCountryCode(string $countryCode, string $produceType): MinMaxPriceDto
    {
        $minPrice = MaizePrice::whereCountry($countryCode)
            ->where('min_local_price', '>', 0)
            ->where('produce_type', $produceType)
            ->min('min_local_price');

        $maxPrice = MaizePrice::whereCountry($countryCode)
            ->where('produce_type', $produceType)
            ->max('max_local_price');

        return new MinMaxPriceDto((float)($minPrice ?? 0), (float)($maxPrice ?? 0));
    }

    /**
     * Load price bands for all (country, produce_type) combinations in one query.
     * Returns an array keyed by "country:produce_type".
     *
     * @return array<string, MinMaxPriceDto>
     */
    public function findPriceBandsBulk(): array
    {
        $results = MaizePrice::query()
            ->selectRaw('country, produce_type, MIN(NULLIF(min_local_price, 0)) as min_price, MAX(max_local_price) as max_price')
            ->groupBy('country', 'produce_type')
            ->get();

        $map = [];
        foreach ($results as $row) {
            $map["{$row->country}:{$row->produce_type}"] = new MinMaxPriceDto(
                (float)($row->min_price ?? 0),
                (float)($row->max_price ?? 0)
            );
        }

        return $map;
    }

    /**
     * Load price bands for a specific set of country codes in one query.
     * Returns an array keyed by "country:produce_type".
     *
     * @param string[] $countries
     * @return array<string, MinMaxPriceDto>
     */
    public function findPriceBandsBulkForCountries(array $countries): array
    {
        $results = MaizePrice::query()
            ->selectRaw('country, produce_type, MIN(NULLIF(min_local_price, 0)) as min_price, MAX(max_local_price) as max_price')
            ->whereIn('country', $countries)
            ->groupBy('country', 'produce_type')
            ->get();

        $map = [];
        foreach ($results as $row) {
            $map["{$row->country}:{$row->produce_type}"] = new MinMaxPriceDto(
                (float)($row->min_price ?? 0),
                (float)($row->max_price ?? 0)
            );
        }

        return $map;
    }
}
