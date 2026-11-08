<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StarchPriceRequest;
use App\Repositories\StarchFactoryRepo;
use App\Repositories\StarchPriceRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StarchPriceController extends AdminController
{
    public function __construct(
        protected StarchPriceRepo $repo,
        protected StarchFactoryRepo $factoryRepo,
    ) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['country', 'starch_factory_id']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'price_class',
            direction: 'asc',
            filters: ['starch_factory_id' => $values['starch_factory_id']],
            // Prices have no country of their own; it is their factory's
            relationFilters: ['starchFactory' => ['country' => $values['country']]],
        );

        return Inertia::render('StarchPrices/Index', [
            'filters' => $values,
            'factories' => $this->factories(),
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'starch_factory_id' => $f->starch_factory_id,
                'price_class' => $f->price_class,
                'min_starch' => $f->min_starch,
                'range_starch' => $f->range_starch,
                'price' => $f->price,
                'currency' => $f->currency,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('StarchPrices/Create', [
            'factories' => $this->factories(),
        ]);
    }

    public function store(StarchPriceRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.starch-prices.index')
            ->with('success', 'Starch price created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('StarchPrices/Edit', [
            'item' => $item->toArray(),
            'factories' => $this->factories(),
        ]);
    }

    public function update(StarchPriceRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.starch-prices.index')
            ->with('success', 'Starch price updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.starch-prices.index')
            ->with('success', 'Starch price deleted.');
    }

    // ── Batch operations ───────────────────────────────────────────────────────

    public function batchCreate(): Response
    {
        return Inertia::render('StarchPrices/BatchCreate', [
            'factories' => $this->factories(),
        ]);
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'factory_id' => ['required', 'integer', 'exists:starch_factories,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.price_class' => ['required', 'integer', 'min:0'],
            'rows.*.min_starch' => ['required', 'numeric', 'min:0'],
            'rows.*.range_starch' => ['nullable', 'string', 'max:50'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.currency' => ['nullable', 'string', 'max:10'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['rows'] as $row) {
                $this->repo->create(array_merge($row, [
                    'starch_factory_id' => $validated['factory_id'],
                ]));
            }
        });

        return redirect()->route('admin.starch-prices.index')
            ->with('success', count($validated['rows']).' starch price(s) created.');
    }

    public function batchEdit(Request $request): Response
    {
        $factoryId = $request->integer('factory_id', 0) ?: null;

        $prices = $factoryId
            ? $this->repo->forFactory($factoryId)->map(fn ($p) => [
                'id' => $p->id,
                'starch_factory_id' => $p->starch_factory_id,
                'price_class' => $p->price_class,
                'min_starch' => $p->min_starch,
                'range_starch' => $p->range_starch ?? '',
                'price' => $p->price,
                'currency' => $p->currency ?? '',
            ])->all()
            : [];

        return Inertia::render('StarchPrices/BatchEdit', [
            'factories' => $this->factories(),
            'prices' => $prices,
            'factory_id' => $factoryId,
        ]);
    }

    public function batchUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'factory_id' => ['required', 'integer', 'exists:starch_factories,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.price_class' => ['required', 'integer', 'min:0'],
            'rows.*.min_starch' => ['required', 'numeric', 'min:0'],
            'rows.*.range_starch' => ['nullable', 'string', 'max:50'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.currency' => ['nullable', 'string', 'max:10'],
            'deleted_ids' => ['sometimes', 'array'],
            'deleted_ids.*' => ['integer'],
        ]);

        $factoryId = $validated['factory_id'];

        DB::transaction(function () use ($validated, $factoryId) {
            // Delete removed rows (scoped to this factory for safety)
            if (! empty($validated['deleted_ids'])) {
                $this->repo->deleteByIds($validated['deleted_ids'], $factoryId);
            }

            foreach ($validated['rows'] as $row) {
                $fields = [
                    'starch_factory_id' => $factoryId,
                    'price_class' => $row['price_class'],
                    'min_starch' => $row['min_starch'],
                    'range_starch' => $row['range_starch'] ?? null,
                    'price' => $row['price'],
                    'currency' => $row['currency'] ?? null,
                ];

                if (! empty($row['id'])) {
                    $this->repo->update($row['id'], $fields);
                } else {
                    $this->repo->create($fields);
                }
            }
        });

        return redirect()->route('admin.starch-prices.index')
            ->with('success', 'Starch prices updated.');
    }

    /** @return array<int, array{id: int, factory_name: string, country: string}> */
    private function factories(): array
    {
        return $this->factoryRepo->all()->map(fn ($f) => [
            'id' => $f->id,
            'factory_name' => $f->factory_name,
            'country' => $f->country,
        ])->all();
    }
}
