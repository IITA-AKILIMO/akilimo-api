<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Support\PriceLists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A read-only view of the price lists the mobile app reads, as the database
 * computes them from the catalogue. They are views, so they cannot be edited:
 * change a catalogue price (default prices) or a rule (price settings) and they
 * follow. This screen shows what the app would be given.
 */
class PriceListController extends AdminController
{
    private const LIMIT = 1000;

    public function index(Request $request): Response
    {
        $list = (string) $request->get('list', 'fertilizer');
        $list = isset(PriceLists::LISTS[$list]) ? $list : 'fertilizer';
        $def = PriceLists::LISTS[$list];
        $country = strtoupper(trim((string) $request->get('country', '')));

        $query = DB::table($def['table']);

        $select = [];
        foreach (array_keys($def['columns']) as $column) {
            $select[] = isset($def['raw'][$column]) ? DB::raw("{$def['raw'][$column]} AS `{$column}`") : $column;
        }
        $query->select($select);

        if ($country !== '') {
            $query->where($def['country'], $country);
        }

        $query->orderBy($def['country']);
        foreach ($def['order'] as $column) {
            $query->orderBy($column);
        }

        $total = (clone $query)->count();

        return Inertia::render('PriceLists/Index', [
            'lists' => collect(PriceLists::LISTS)->map(fn ($d, $key) => ['key' => $key, 'title' => $d['title']])->values(),
            'list' => $list,
            'country' => $country,
            'title' => $def['title'],
            'columns' => collect($def['columns'])->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'rows' => $query->limit(self::LIMIT)->get(),
            'total' => $total,
            'limit' => self::LIMIT,
        ]);
    }
}
