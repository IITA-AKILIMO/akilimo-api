<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CassavaPriceRequest;
use App\Repositories\CassavaPriceRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CassavaPriceController extends AdminController
{
    public function __construct(protected CassavaPriceRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['country', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: ['country' => $values['country'], 'price_active' => $values['active']],
        );

        return Inertia::render('CassavaPrices/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'country' => $f->country,
                'min_price' => $f->min_price,
                'max_price' => $f->max_price,
                'min_local_price' => $f->min_local_price,
                'max_local_price' => $f->max_local_price,
                'min_usd' => $f->min_usd,
                'max_usd' => $f->max_usd,
                'price_active' => (bool) $f->price_active,
                'sort_order' => $f->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CassavaPrices/Create');
    }

    public function store(CassavaPriceRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.cassava-prices.index')
            ->with('success', 'Cassava price created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('CassavaPrices/Edit', ['item' => $item->toArray()]);
    }

    public function update(CassavaPriceRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.cassava-prices.index')
            ->with('success', 'Cassava price updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.cassava-prices.index')
            ->with('success', 'Cassava price deleted.');
    }
}
