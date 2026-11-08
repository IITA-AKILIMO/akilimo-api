<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\InvestmentAmountRequest;
use App\Repositories\InvestmentRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvestmentAmountController extends AdminController
{
    public function __construct(protected InvestmentRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['country', 'area_unit', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: [
                'country' => $values['country'],
                'area_unit' => $values['area_unit'],
                'price_active' => $values['active'],
            ],
        );

        return Inertia::render('InvestmentAmounts/Index', [
            'filters' => $values,
            'areaUnits' => $this->distinctValues('investment_amount', 'area_unit'),
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'country' => $f->country,
                'investment_amount' => $f->investment_amount,
                'area_unit' => $f->area_unit,
                'price_active' => (bool) $f->price_active,
                'sort_order' => $f->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('InvestmentAmounts/Create');
    }

    public function store(InvestmentAmountRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.investment-amounts.index')
            ->with('success', 'Investment amount created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('InvestmentAmounts/Edit', ['item' => $item->toArray()]);
    }

    public function update(InvestmentAmountRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.investment-amounts.index')
            ->with('success', 'Investment amount updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.investment-amounts.index')
            ->with('success', 'Investment amount deleted.');
    }
}
