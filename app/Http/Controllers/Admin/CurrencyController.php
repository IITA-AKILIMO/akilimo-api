<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CurrencyRequest;
use App\Repositories\CurrencyRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurrencyController extends AdminController
{
    public function __construct(protected CurrencyRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'country_code',
            direction: 'asc',
            // country_code is the DB column; request sends 'country'
            filters: ['country_code' => $values['country']],
            search: $values['search'],
            searchColumns: ['currency_name', 'currency_code', 'country'],
        );

        return Inertia::render('Currencies/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'country_code' => $f->country_code,
                'country' => $f->country,
                'currency_name' => $f->currency_name,
                'currency_code' => $f->currency_code,
                'currency_symbol' => $f->currency_symbol,
                'currency_native_symbol' => $f->currency_native_symbol,
                'name_plural' => $f->name_plural,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Currencies/Create');
    }

    public function store(CurrencyRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('Currencies/Edit', ['item' => $item->toArray()]);
    }

    public function update(CurrencyRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency deleted.');
    }
}
