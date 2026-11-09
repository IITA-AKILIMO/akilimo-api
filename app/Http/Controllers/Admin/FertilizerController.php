<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FertilizerRequest;
use App\Repositories\FertilizerRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FertilizerController extends AdminController
{
    public function __construct(protected FertilizerRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country', 'use_case', 'available']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: [
                'country' => $values['country'],
                'use_case' => $values['use_case'],
                'available' => in_array($values['available'], ['0', '1'], true) ? $values['available'] : '',
            ],
            search: $values['search'],
            searchColumns: ['name', 'type', 'fertilizer_key', 'fertilizer_label'],
        );

        return Inertia::render('Fertilizers/Index', [
            'filters' => $values,
            'useCases' => $this->distinctValues('fertilizers', 'use_case'),
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'type' => $f->type,
                'fertilizer_key' => $f->fertilizer_key,
                'fertilizer_label' => $f->fertilizer_label,
                'country' => $f->country,
                'weight' => $f->weight,
                'use_case' => $f->use_case,
                'available' => (bool)$f->available,
                'cis' => (bool)$f->cis,
                'cim' => (bool)$f->cim,
                'sort_order' => $f->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Fertilizers/Create');
    }

    public function store(FertilizerRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.fertilizers.index')
            ->with('success', 'Fertilizer created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('Fertilizers/Edit', ['item' => $item->toArray()]);
    }

    public function update(FertilizerRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.fertilizers.index')
            ->with('success', 'Fertilizer updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.fertilizers.index')
            ->with('success', 'Fertilizer deleted.');
    }
}
