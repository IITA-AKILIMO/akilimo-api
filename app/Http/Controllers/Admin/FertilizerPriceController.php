<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FertilizerPriceRequest;
use App\Repositories\FertilizerPriceRepo;
use App\Repositories\FertilizerRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FertilizerPriceController extends AdminController
{
    public function __construct(
        protected FertilizerPriceRepo $repo,
        protected FertilizerRepo $fertilizerRepo,
    ) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: ['country' => $values['country'], 'price_active' => $values['active']],
            // Was an exact fertilizer_key match; part of a key ("NPK", "UREA") now finds it
            search: $values['search'],
            searchColumns: ['fertilizer_key', 'desc'],
        );

        return Inertia::render('FertilizerPrices/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'country' => $f->country,
                'fertilizer_key' => $f->fertilizer_key,
                'min_price' => $f->min_price,
                'max_price' => $f->max_price,
                'price_per_bag' => $f->price_per_bag,
                'price_active' => (bool)$f->price_active,
                'sort_order' => $f->sort_order,
                'desc' => $f->desc,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('FertilizerPrices/Create');
    }

    public function store(FertilizerPriceRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.fertilizer-prices.index')
            ->with('success', 'Fertilizer price created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('FertilizerPrices/Edit', ['item' => $item->toArray()]);
    }

    public function update(FertilizerPriceRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.fertilizer-prices.index')
            ->with('success', 'Fertilizer price updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.fertilizer-prices.index')
            ->with('success', 'Fertilizer price deleted.');
    }

    // ── Batch operations ───────────────────────────────────────────────────────

    public function batchCreate(): Response
    {
        return Inertia::render('FertilizerPrices/BatchCreate');
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.fertilizer_key' => ['required', 'string', 'max:50'],
            'rows.*.min_price' => ['required', 'numeric', 'min:0'],
            'rows.*.max_price' => ['required', 'numeric', 'min:0'],
            'rows.*.price_per_bag' => ['required', 'numeric', 'min:0'],
            'rows.*.price_active' => ['boolean'],
            'rows.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'rows.*.desc' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['rows'] as $row) {
                $this->repo->create(array_merge($row, [
                    'country' => $validated['country'],
                ]));
            }
        });

        return redirect()->route('admin.fertilizer-prices.index')
            ->with('success', count($validated['rows']) . ' fertilizer price(s) created.');
    }

    public function batchEdit(Request $request): Response
    {
        $country = $request->get('country', '');

        $prices = [];
        $fertilizerTypes = [];

        if ($country) {
            $prices = $this->repo->forCountry($country)->map(fn ($p) => [
                'id' => $p->id,
                'country' => $p->country,
                'fertilizer_key' => $p->fertilizer_key,
                'min_price' => $p->min_price,
                'max_price' => $p->max_price,
                'price_per_bag' => $p->price_per_bag,
                'price_active' => (bool)$p->price_active,
                'sort_order' => $p->sort_order,
                'desc' => $p->desc ?? '',
            ])->all();

            // Build key → type map so the UI can filter rows by fertilizer type
            $fertilizerTypes = $this->fertilizerRepo->forCountry($country)
                ->mapWithKeys(fn ($f) => [$f->fertilizer_key => $f->type])
                ->all();
        }

        return Inertia::render('FertilizerPrices/BatchEdit', [
            'prices' => $prices,
            'country' => $country ?: null,
            'fertilizerTypes' => $fertilizerTypes,
        ]);
    }

    public function batchUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.fertilizer_key' => ['required', 'string', 'max:50'],
            'rows.*.min_price' => ['required', 'numeric', 'min:0'],
            'rows.*.max_price' => ['required', 'numeric', 'min:0'],
            'rows.*.price_per_bag' => ['required', 'numeric', 'min:0'],
            'rows.*.price_active' => ['boolean'],
            'rows.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'rows.*.desc' => ['nullable', 'string', 'max:255'],
            'deleted_ids' => ['sometimes', 'array'],
            'deleted_ids.*' => ['integer'],
        ]);

        $country = $validated['country'];

        DB::transaction(function () use ($validated, $country) {
            if (!empty($validated['deleted_ids'])) {
                $this->repo->deleteByIds($validated['deleted_ids'], $country);
            }

            foreach ($validated['rows'] as $row) {
                $fields = [
                    'country' => $country,
                    'fertilizer_key' => $row['fertilizer_key'],
                    'min_price' => $row['min_price'],
                    'max_price' => $row['max_price'],
                    'price_per_bag' => $row['price_per_bag'],
                    'price_active' => $row['price_active'] ?? false,
                    'sort_order' => $row['sort_order'] ?? null,
                    'desc' => $row['desc'] ?? null,
                ];

                if (!empty($row['id'])) {
                    $this->repo->update($row['id'], $fields);
                } else {
                    $this->repo->create($fields);
                }
            }
        });

        return redirect()->route('admin.fertilizer-prices.index')
            ->with('success', 'Fertilizer prices updated.');
    }
}
