<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reads the price list views the app is served from (see App\Support\PriceLists). They have no model: a view is read by name.
 */
class PriceListViewRepo
{
    /**
     * Every row of a list view, ordered by its country column and then by $order.
     *
     * @param list<string> $order
     * @return Collection<int, \stdClass>
     */
    public function rows(string $table, string $countryColumn, array $order): Collection
    {
        $query = DB::table($table)->orderBy($countryColumn);

        foreach ($order as $column) {
            $query->orderBy($column);
        }

        return $query->get();
    }
}
