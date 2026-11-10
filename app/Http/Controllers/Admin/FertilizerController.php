<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FertilizerRequest;
use App\Models\FertilizerType;
use App\Repositories\FertilizerRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return Inertia::render('Fertilizers/Create', ['types' => $this->types(), 'useCases' => $this->useCases()]);
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

        return Inertia::render('Fertilizers/Edit', ['item' => $item->toArray(), 'types' => $this->types(), 'useCases' => $this->useCases()]);
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
            ->with('success', 'Fertilizer removed from the country.');
    }

    /**
     * What a fertiliser is offered for, from fertilizer_use_cases.
     *
     * @return list<array{value: string, label: string}>
     */
    private function useCases(): array
    {
        return DB::table('fertilizer_use_cases')->orderBy('sort_order')->get(['value', 'label'])
            ->map(fn ($u) => ['value' => (string) $u->value, 'label' => (string) $u->label])
            ->all();
    }

    /**
     * The fertilisers a country can sell, from fertilizer_types: picked in the form
     * instead of typed. A new fertiliser is a row in that table.
     *
     * @return list<array{type: string, name: string, weight: int}>
     */
    private function types(): array
    {
        return FertilizerType::query()->orderBy('name')->get(['type', 'name', 'weight'])
            ->map(fn (FertilizerType $t) => ['type' => $t->type, 'name' => $t->name, 'weight' => $t->weight])
            ->all();
    }
}
