<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\PriceItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * The items a catalogue price can be for (price_items), keyed by their name.
 *
 * @extends BaseRepo<PriceItem>
 */
class PriceItemRepo extends BaseRepo
{
    protected function model(): string
    {
        return PriceItem::class;
    }

    /** The item as spelled in the table, or null when there is none */
    public function canonicalName(string $item): ?string
    {
        $name = $this->query()->where('item', $item)->value('item');

        return $name === null ? null : (string)$name;
    }

    /** The unit an item is priced in, or null when the item is not listed */
    public function unitOf(string $item): ?string
    {
        $unit = $this->query()->where('item', $item)->value('unit');

        return $unit === null ? null : (string)$unit;
    }

    /** Make sure an item exists (names match without regard to case); the name is the table's key, so it is set apart from the mass assignable columns */
    public function ensure(string $item, string $unit, string $category): void
    {
        if ($this->existsWithConditions(['item' => $item])) {
            return;
        }

        $row = $this->model->newInstance(['unit' => $unit, 'category' => $category]);
        $row->item = $item;
        $row->save();
    }

    /**
     * Item and unit of every item, by name.
     *
     * @return Collection<int, PriceItem>
     */
    public function listing(): Collection
    {
        return $this->query()->orderBy('item')->get(['item', 'unit']);
    }
}
