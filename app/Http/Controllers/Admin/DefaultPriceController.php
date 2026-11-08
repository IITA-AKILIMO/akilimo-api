<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DefaultPriceRequest;
use App\Repositories\DefaultPriceRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DefaultPriceController extends AdminController
{
    public function __construct(protected DefaultPriceRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'country',
            direction: 'asc',
            filters: ['country' => $values['country']],
            search: $values['search'],
            searchColumns: ['item', 'unit', 'currency'],
        );

        return Inertia::render('DefaultPrices/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'country' => $f->country,
                'item' => $f->item,
                'price' => $f->price,
                'unit' => $f->unit,
                'currency' => $f->currency,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DefaultPrices/Create');
    }

    public function store(DefaultPriceRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default price created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('DefaultPrices/Edit', ['item' => $item->toArray()]);
    }

    public function update(DefaultPriceRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default price updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default price deleted.');
    }

    // ── Batch operations ───────────────────────────────────────────────────────

    public function batchCreate(): Response
    {
        return Inertia::render('DefaultPrices/BatchCreate');
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.item' => ['required', 'string', 'max:50'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.unit' => ['required', 'string', 'max:15'],
            'rows.*.currency' => ['nullable', 'string', 'size:3'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['rows'] as $row) {
                $this->repo->create(array_merge($row, [
                    'country' => $validated['country'],
                ]));
            }
        });

        return redirect()->route('admin.default-prices.index')
            ->with('success', count($validated['rows']).' default price(s) created.');
    }

    public function batchEdit(Request $request): Response
    {
        $country = $request->get('country', '');

        $prices = $country
            ? $this->repo->forCountry($country)->map(fn ($p) => [
                'id' => $p->id,
                'country' => $p->country,
                'item' => $p->item,
                'price' => $p->price,
                'unit' => $p->unit,
                'currency' => $p->currency ?? '',
            ])->all()
            : [];

        return Inertia::render('DefaultPrices/BatchEdit', [
            'prices' => $prices,
            'country' => $country ?: null,
        ]);
    }

    public function batchUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.item' => ['required', 'string', 'max:50'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.unit' => ['required', 'string', 'max:15'],
            'rows.*.currency' => ['nullable', 'string', 'size:3'],
            'deleted_ids' => ['sometimes', 'array'],
            'deleted_ids.*' => ['integer'],
        ]);

        $country = $validated['country'];

        DB::transaction(function () use ($validated, $country) {
            if (! empty($validated['deleted_ids'])) {
                $this->repo->deleteByIds($validated['deleted_ids'], $country);
            }

            foreach ($validated['rows'] as $row) {
                $fields = [
                    'country' => $country,
                    'item' => $row['item'],
                    'price' => $row['price'],
                    'unit' => $row['unit'],
                    'currency' => $row['currency'] ?? null,
                ];

                if (! empty($row['id'])) {
                    $this->repo->update($row['id'], $fields);
                } else {
                    $this->repo->create($fields);
                }
            }
        });

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default prices updated.');
    }
}
