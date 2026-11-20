<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\VwOperationCost;
use Illuminate\Database\Eloquent\Collection;

class OperationCostRepo extends BaseRepo
{
    protected function model(): string
    {
        return VwOperationCost::class;
    }

    /**
     * @return Collection<int, VwOperationCost>
     */
    public function forCountry(string $countryCode): Collection
    {
        return $this->query()
            ->where('country_code', $countryCode)
            ->orderBy('operation_type')
            ->orderBy('operation_name')
            ->get();
    }

    public function deleteByIds(array $ids, string $countryCode): void
    {
        if (empty($ids)) {
            return;
        }

        $this->query()
            ->whereIn('id', $ids)
            ->where('country_code', $countryCode)
            ->delete();
    }
}
