<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CassavaUnitRequest;
use App\Repositories\CassavaUnitRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CassavaUnitController extends AdminController
{
    public function __construct(protected CassavaUnitRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: ['is_active' => $values['active']],
            search: $values['search'],
            searchColumns: ['label', 'description'],
        );

        return Inertia::render('CassavaUnits/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'label' => $f->label,
                'unit_weight' => $f->unit_weight,
                'description' => $f->description,
                'is_active' => (bool)$f->is_active,
                'sort_order' => $f->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CassavaUnits/Create');
    }

    public function store(CassavaUnitRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.cassava-units.index')
            ->with('success', 'Cassava unit created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('CassavaUnits/Edit', ['item' => $item->toArray()]);
    }

    public function update(CassavaUnitRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.cassava-units.index')
            ->with('success', 'Cassava unit updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.cassava-units.index')
            ->with('success', 'Cassava unit deleted.');
    }
}
