<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\PriceBasis;
use App\Http\Requests\Admin\CountryRequest;
use App\Models\Country;
use App\Repositories\CountryRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CountryController extends AdminController
{
    public function __construct(protected CountryRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: ['active' => $values['active']],
            search: $values['search'],
            searchColumns: ['code', 'name'],
        );

        return Inertia::render('Countries/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'active' => (bool)$c->active,
                'default_price_basis' => $c->default_price_basis?->value,
                'sort_order' => $c->sort_order,
                'has_boundary' => $c->boundary !== null,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Countries/Create', [
            'basisOptions' => $this->basisOptions(),
        ]);
    }

    public function store(CountryRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.countries.index')
            ->with('success', 'Country created.');
    }

    public function edit(int $id): Response
    {
        /** @var Country $item */
        $item = $this->repo->findOrFail($id);

        return Inertia::render('Countries/Edit', [
            'item' => [
                ...$item->toArray(),
                'default_price_basis' => $item->default_price_basis?->value ?? 'open_market',
            ],
            'basisOptions' => $this->basisOptions(),
        ]);
    }

    public function update(CountryRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.countries.index')
            ->with('success', 'Country updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.countries.index')
            ->with('success', 'Country deleted.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function basisOptions(): array
    {
        return array_map(
            fn (PriceBasis $basis) => ['value' => $basis->value, 'label' => $basis->label()],
            PriceBasis::cases(),
        );
    }
}
