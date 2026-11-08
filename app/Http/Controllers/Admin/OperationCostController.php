<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\OperationCostRequest;
use App\Repositories\OperationCostRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OperationCostController extends AdminController
{
    public function __construct(protected OperationCostRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country', 'active']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'created_at',
            direction: 'asc',
            // country_code is the DB column; request sends 'country'
            filters: ['country_code' => $values['country'], 'is_active' => $values['active']],
            search: $values['search'],
            searchColumns: ['operation_name', 'operation_type'],
        );

        return Inertia::render('OperationCosts/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'operation_name' => $f->operation_name,
                'operation_type' => $f->operation_type,
                'country_code' => $f->country_code,
                'min_cost' => $f->min_cost,
                'max_cost' => $f->max_cost,
                'is_active' => (bool) $f->is_active,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OperationCosts/Create');
    }

    public function store(OperationCostRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.operation-costs.index')
            ->with('success', 'Operation cost created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('OperationCosts/Edit', ['item' => $item->toArray()]);
    }

    public function update(OperationCostRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.operation-costs.index')
            ->with('success', 'Operation cost updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.operation-costs.index')
            ->with('success', 'Operation cost deleted.');
    }

    // ── Batch operations ───────────────────────────────────────────────────────

    public function batchCreate(): Response
    {
        return Inertia::render('OperationCosts/BatchCreate');
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.operation_name' => ['required', 'string', 'max:100'],
            'rows.*.operation_type' => ['required', 'string', 'max:50'],
            'rows.*.min_cost' => ['required', 'numeric', 'min:0'],
            'rows.*.max_cost' => ['required', 'numeric', 'min:0'],
            'rows.*.is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['rows'] as $row) {
                $this->repo->create(array_merge($row, [
                    'country_code' => $validated['country'],
                ]));
            }
        });

        return redirect()->route('admin.operation-costs.index')
            ->with('success', count($validated['rows']).' operation cost(s) created.');
    }

    public function batchEdit(Request $request): Response
    {
        $country = $request->get('country', '');

        $costs = $country
            ? $this->repo->forCountry($country)->map(fn ($c) => [
                'id' => $c->id,
                'country_code' => $c->country_code,
                'operation_name' => $c->operation_name,
                'operation_type' => $c->operation_type,
                'min_cost' => $c->min_cost,
                'max_cost' => $c->max_cost,
                'is_active' => (bool) $c->is_active,
            ])->all()
            : [];

        return Inertia::render('OperationCosts/BatchEdit', [
            'costs' => $costs,
            'country' => $country ?: null,
        ]);
    }

    public function batchUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.operation_name' => ['required', 'string', 'max:100'],
            'rows.*.operation_type' => ['required', 'string', 'max:50'],
            'rows.*.min_cost' => ['required', 'numeric', 'min:0'],
            'rows.*.max_cost' => ['required', 'numeric', 'min:0'],
            'rows.*.is_active' => ['boolean'],
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
                    'country_code' => $country,
                    'operation_name' => $row['operation_name'],
                    'operation_type' => $row['operation_type'],
                    'min_cost' => $row['min_cost'],
                    'max_cost' => $row['max_cost'],
                    'is_active' => $row['is_active'] ?? false,
                ];

                if (! empty($row['id'])) {
                    $this->repo->update($row['id'], $fields);
                } else {
                    $this->repo->create($fields);
                }
            }
        });

        return redirect()->route('admin.operation-costs.index')
            ->with('success', 'Operation costs updated.');
    }
}
