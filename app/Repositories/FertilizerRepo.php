<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\CountryFertilizer;
use App\Models\VwFertilizer;
use App\Models\FertilizerType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Reads the fertilizers view; writes go to fertilizer_types and
 * country_fertilizers, which the view is built from (migrations 000025 to
 * 000027). A write names the fertiliser by its type code (UREA) and the country;
 * the fertilizer key is computed from them and cannot be set.
 *
 * @extends BaseRepo<VwFertilizer>
 */
class FertilizerRepo extends BaseRepo
{
    protected function model(): string
    {
        return VwFertilizer::class;
    }

    /**
     * @return Collection<int, VwFertilizer>
     */
    public function forCountry(string $country): Collection
    {
        return $this->query()
            ->where('country', $country)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * A scope for paginateWithSort() that keeps only the fertilizers with an active
     * price in their country, so a client is never offered one it cannot price. A
     * price matches by the fertilizer type, or by the catalogue label for a price
     * that is not linked to a type yet.
     *
     * @return \Closure(\Illuminate\Database\Eloquent\Builder<VwFertilizer>): void
     */
    public function onlyWithActivePrice(): \Closure
    {
        return function ($query): void {
            $query->whereExists(function ($price) {
                $price->selectRaw('1')
                    ->from('price_catalogue')
                    ->whereColumn('price_catalogue.country', 'vw_fertilizers.country')
                    ->where('price_catalogue.status', 'active')
                    ->where(function ($match) {
                        $match->whereColumn('price_catalogue.fertilizer_type', 'vw_fertilizers.type')
                            ->orWhereColumn('price_catalogue.item', 'vw_fertilizers.fertilizer_label');
                    });
            });
        };
    }

    /**
     * Sell a fertiliser type in a country. $data holds a type code that exists
     * in fertilizer_types, the country, and the country's use_case, cis, cim,
     * available and sort_order.
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $row = CountryFertilizer::create($this->columns($data) + ['fertilizer_type' => $data['type']]);
            $this->setCategory($row->fertilizer_type, $data);

            return $this->findOrFail($row->id);
        });
    }

    public function update(int|string $id, array $data): ?Model
    {
        return DB::transaction(function () use ($id, $data) {
            $row = CountryFertilizer::findOrFail($id);
            $columns = $this->columns($data);

            if (isset($data['type'])) {
                $columns['fertilizer_type'] = $data['type'];
            }

            $row->update($columns);
            $this->setCategory($row->fertilizer_type, $data);

            return $this->findOrFail($row->id);
        });
    }

    /**
     * Stop selling the fertiliser in that country. The fertiliser type stays.
     */
    public function delete(int|string $id): bool
    {
        return (bool) CountryFertilizer::findOrFail($id)->delete();
    }

    /**
     * The category belongs to the fertiliser, so it is saved on the type and applies in every country.
     */
    private function setCategory(string $type, array $data): void
    {
        if (isset($data['category'])) {
            FertilizerType::query()->where('type', $type)->update(['category' => $data['category']]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function columns(array $data): array
    {
        return array_intersect_key($data, array_flip(['country', 'use_case', 'cis', 'cim', 'available', 'sort_order']));
    }
}
