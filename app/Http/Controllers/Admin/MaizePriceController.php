<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MaizePriceRequest;
use App\Repositories\MaizePriceRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaizePriceController extends AdminController
{
    public function __construct(protected MaizePriceRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['country', 'produce_type', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: [
                'country' => $values['country'],
                'produce_type' => $values['produce_type'],
                'price_active' => $values['active'],
            ],
        );

        return Inertia::render('MaizePrices/Index', [
            'filters' => $values,
            'produceTypes' => $this->distinctValues('maize_prices', 'produce_type'),
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'country' => $f->country,
                'produce_type' => $f->produce_type,
                'min_price' => $f->min_price,
                'max_price' => $f->max_price,
                'min_local_price' => $f->min_local_price,
                'max_local_price' => $f->max_local_price,
                'min_usd' => $f->min_usd,
                'max_usd' => $f->max_usd,
                'price_active' => (bool)$f->price_active,
                'sort_order' => $f->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MaizePrices/Create');
    }

    public function store(MaizePriceRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.maize-prices.index')
            ->with('success', 'Maize price created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('MaizePrices/Edit', ['item' => $item->toArray()]);
    }

    public function update(MaizePriceRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.maize-prices.index')
            ->with('success', 'Maize price updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.maize-prices.index')
            ->with('success', 'Maize price deleted.');
    }
}
