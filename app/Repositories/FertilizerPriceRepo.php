<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\FertilizerPrice;
use Illuminate\Database\Eloquent\Collection;

class FertilizerPriceRepo extends BaseRepo
{
    protected function model(): string
    {
        return FertilizerPrice::class;
    }

    /**
     * @return Collection<int, FertilizerPrice>
     */
    public function forCountry(string $country): Collection
    {
        return $this->query()
            ->where('country', $country)
            ->orderBy('sort_order')
            ->orderBy('fertilizer_key')
            ->get();
    }

    public function deleteByIds(array $ids, string $country): void
    {
        if (empty($ids)) {
            return;
        }

        $this->query()
            ->whereIn('id', $ids)
            ->where('country', $country)
            ->delete();
    }

    /**
     * Load min (sort_order=1) and max (sort_order=4) price band records for a list of
     * fertilizer keys in two queries instead of two queries per row.
     *
     * @param string[] $keys
     * @return array<string, array{min: FertilizerPrice|null, max: FertilizerPrice|null}>
     */
    public function findMinMaxBandsByKeys(array $keys): array
    {
        $minBands = FertilizerPrice::where('sort_order', 1)
            ->whereIn('fertilizer_key', $keys)
            ->get()
            ->keyBy('fertilizer_key');

        $maxBands = FertilizerPrice::where('sort_order', 4)
            ->whereIn('fertilizer_key', $keys)
            ->get()
            ->keyBy('fertilizer_key');

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = [
                'min' => $minBands->get($key),
                'max' => $maxBands->get($key),
            ];
        }

        return $result;
    }
}
