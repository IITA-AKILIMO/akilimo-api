<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

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
    /** @var array<string, array{title: string, table: string, country: string, order: list<string>, columns: array<string, string>, raw?: array<string, string>}> */
    private const LISTS = [
        'fertilizer' => [
            'title' => 'Fertilizer prices', 'table' => 'fertilizer_prices', 'country' => 'country',
            'order' => ['fertilizer_key', 'sort_order'],
            'columns' => ['country' => 'Country', 'fertilizer_key' => 'Fertilizer key', 'price_per_bag' => 'Price per bag', 'sort_order' => 'Order', 'desc' => 'Choice'],
        ],
        'cassava' => [
            'title' => 'Cassava prices', 'table' => 'cassava_prices', 'country' => 'country',
            'order' => ['min_local_price'],
            'columns' => ['country' => 'Country', 'min_local_price' => 'From', 'max_local_price' => 'To', 'average' => 'Average sent'],
            'raw' => ['average' => '(min_local_price + max_local_price) / 2'],
        ],
        'maize' => [
            'title' => 'Maize prices', 'table' => 'maize_prices', 'country' => 'country',
            'order' => ['produce_type', 'min_local_price'],
            'columns' => ['country' => 'Country', 'produce_type' => 'Produce', 'min_local_price' => 'From', 'max_local_price' => 'To', 'average' => 'Average sent'],
            'raw' => ['average' => '(min_local_price + max_local_price) / 2'],
        ],
        'potato' => [
            'title' => 'Sweet potato prices', 'table' => 'potato_prices', 'country' => 'country',
            'order' => ['min_local_price'],
            'columns' => ['country' => 'Country', 'min_local_price' => 'From', 'max_local_price' => 'To', 'average' => 'Average sent'],
            'raw' => ['average' => '(min_local_price + max_local_price) / 2'],
        ],
        'investment' => [
            'title' => 'Investment amounts', 'table' => 'investment_amount', 'country' => 'country',
            'order' => ['sort_order'],
            'columns' => ['country' => 'Country', 'investment_amount' => 'Amount', 'area_unit' => 'Per', 'sort_order' => 'Order'],
        ],
        'operations' => [
            'title' => 'Operation costs', 'table' => 'operation_costs', 'country' => 'country_code',
            'order' => ['operation_name', 'operation_type', 'min_cost'],
            'columns' => ['country_code' => 'Country', 'operation_name' => 'Operation', 'operation_type' => 'Type', 'min_cost' => 'From', 'max_cost' => 'To'],
        ],
    ];

    private const LIMIT = 1000;

    public function index(Request $request): Response
    {
        $list = (string) $request->get('list', 'fertilizer');
        $def = self::LISTS[$list] ?? self::LISTS['fertilizer'];
        $list = isset(self::LISTS[$list]) ? $list : 'fertilizer';
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
            'lists' => collect(self::LISTS)->map(fn ($d, $key) => ['key' => $key, 'title' => $d['title']])->values(),
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
