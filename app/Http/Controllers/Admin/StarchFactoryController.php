<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StarchFactoryRequest;
use App\Repositories\StarchFactoryRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StarchFactoryController extends AdminController
{
    public function __construct(protected StarchFactoryRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: ['country' => $values['country'], 'factory_active' => $values['active']],
            search: $values['search'],
            searchColumns: ['factory_name', 'factory_label'],
        );

        return Inertia::render('StarchFactories/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'factory_name' => $f->factory_name,
                'factory_label' => $f->factory_label,
                'country' => $f->country,
                'factory_active' => (bool) $f->factory_active,
                'sort_order' => $f->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('StarchFactories/Create');
    }

    public function store(StarchFactoryRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.starch-factories.index')
            ->with('success', 'Starch factory created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('StarchFactories/Edit', ['item' => $item->toArray()]);
    }

    public function update(StarchFactoryRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.starch-factories.index')
            ->with('success', 'Starch factory updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.starch-factories.index')
            ->with('success', 'Starch factory deleted.');
    }
}
