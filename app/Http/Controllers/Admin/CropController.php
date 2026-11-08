<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CropRequest;
use App\Repositories\CropRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CropController extends AdminController
{
    public function __construct(protected CropRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country', 'active', 'advice']);
        $country = strtoupper($values['country']);
        $paginator = $this->repo->paginateWithSort(
            perPage: (int) $request->get('per_page', 20),
            orderBy: 'sort_order',
            direction: 'asc',
            filters: [
                'active' => $values['active'],
                'advice_available' => in_array($values['advice'], ['0', '1'], true) ? $values['advice'] : '',
            ],
            search: $values['search'],
            searchColumns: ['crop_key', 'name', 'description'],
            // Offered in a country: no country list (everywhere) or a list that names it
            scope: $country === '' ? null : fn ($q) => $q->where(
                fn ($w) => $w->whereNull('countries')->orWhereJsonContains('countries', $country)
            ),
        );

        return Inertia::render('Crops/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($c) => [
                'id' => $c->id,
                'crop_key' => $c->crop_key,
                'name' => $c->name,
                'description' => $c->description,
                'active' => $c->active,
                'advice_available' => $c->advice_available,
                'countries' => $c->countries ? implode(', ', $c->countries) : 'All',
                'sort_order' => $c->sort_order,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Crops/Create');
    }

    public function store(CropRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.crops.index')->with('success', 'Crop created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('Crops/Edit', [
            'item' => [...$item->toArray(), 'countries' => $item->countries ? implode(', ', $item->countries) : ''],
        ]);
    }

    public function update(CropRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.crops.index')->with('success', 'Crop updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.crops.index')->with('success', 'Crop deleted.');
    }
}
