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
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A read-only view of the price lists the mobile app reads, as the database
 * computes them from the catalogue. They are views, so they cannot be edited:
 * change a catalogue price (the Price catalogue) or a rule (price settings) and they
 * follow. This screen shows what the app would be given, including the label a
 * client displays for each choice.
 */
class PriceListController extends AdminController
{
    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['list', 'country', 'search', 'kind']);

        $list = isset(PriceLists::LISTS[$values['list']]) ? $values['list'] : 'fertilizer';
        $def = PriceLists::LISTS[$list];
        $searchColumns = PriceLists::SEARCH[$list] ?? [];
        $marker = PriceLists::MARKER[$list] ?? null;
        $kind = $marker !== null && in_array($values['kind'], ['choices', 'markers'], true) ? $values['kind'] : '';
        $country = strtoupper($values['country']);

        $query = DB::table($def['table']);

        $select = [];
        foreach (array_keys($def['columns']) as $column) {
            $select[] = isset($def['raw'][$column]) ? DB::raw("{$def['raw'][$column]} AS `{$column}`") : $column;
        }
        // The label needs the columns it is worked out from, whether or not the table shows them
        foreach (['price_per_bag', 'min_local_price', 'max_local_price', 'investment_amount', 'min_cost', 'max_cost'] as $needed) {
            if (! array_key_exists($needed, $def['columns']) && $this->hasColumn($def['table'], $needed)) {
                $select[] = $needed;
            }
        }
        $query->select($select);

        if ($country !== '') {
            $query->where($def['country'], $country);
        }

        if ($values['search'] !== '' && $searchColumns !== []) {
            $term = '%' . addcslashes($values['search'], '%_\\') . '%';
            $query->where(function ($q) use ($searchColumns, $term) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }

        if ($kind === 'markers') {
            $query->whereRaw($marker);
        } elseif ($kind === 'choices') {
            $query->whereRaw("NOT ({$marker})");
        }

        $query->orderBy($def['country']);
        foreach ($def['order'] as $column) {
            $query->orderBy($column);
        }

        $symbols = $this->symbols();
        $paginator = $query->paginate((int) min(100, max(10, (int) $request->get('per_page', 25))))->withQueryString();

        return Inertia::render('PriceLists/Index', [
            'lists' => collect(PriceLists::LISTS)->map(fn ($d, $key) => ['key' => $key, 'title' => $d['title']])->values(),
            'filters' => ['list' => $list, 'country' => $country, 'search' => $values['search'], 'kind' => $kind],
            'title' => $def['title'],
            'searchPlaceholder' => $searchColumns === [] ? null : 'Search ' . implode(', ', array_map(fn ($c) => str_replace('_', ' ', $c), $searchColumns)) . '…',
            'hasMarkers' => $marker !== null,
            'columns' => collect($def['columns'])->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'items' => $this->paginateShape($paginator, function ($row) use ($list, $def, $symbols) {
                $country = (string) $row->{$def['country']};

                return (array) $row + ['label' => PriceLists::label($list, $row, $symbols[$country] ?? null)];
            }),
        ]);
    }

    /**
     * The currency symbol of each country (or its code), as the API labels use it.
     *
     * @return array<string, string>
     */
    private function symbols(): array
    {
        return DB::table('currencies')
            ->whereNotNull('country_code')
            ->orderBy('id')
            ->get(['country_code', 'currency_symbol', 'currency_code'])
            ->unique('country_code')
            ->mapWithKeys(fn ($c) => [(string) $c->country_code => (string) ($c->currency_symbol ?: $c->currency_code)])
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}
