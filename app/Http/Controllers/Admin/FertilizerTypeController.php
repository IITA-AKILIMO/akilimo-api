<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FertilizerTypeRequest;
use App\Http\Requests\Admin\FertilizerTypeUpdateRequest;
use App\Models\FertilizerType;
use App\Service\FertilizerTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Adds a new fertiliser (a fertilizer_types row). Which countries sell it is set
 * on the fertilizers screens afterwards.
 */
class FertilizerTypeController extends AdminController
{
    public function __construct(protected FertilizerTypeService $types) {}

    public function create(): Response
    {
        return Inertia::render('FertilizerTypes/Create', ['categories' => $this->categories()]);
    }

    public function edit(string $type): Response
    {
        $item = FertilizerType::findOrFail($type);

        return Inertia::render('FertilizerTypes/Edit', [
            'item' => $item->only(['type', 'label', 'name', 'weight', 'category']),
            'categories' => $this->categories(),
            'countries' => DB::table('country_fertilizers')->where('fertilizer_type', $item->type)->orderBy('country')->pluck('country')->all(),
        ]);
    }

    public function update(FertilizerTypeUpdateRequest $request, string $type): RedirectResponse
    {
        $item = FertilizerType::findOrFail($type);
        $item->update($request->validated());

        return redirect()->route('admin.fertilizers.index')
            ->with('success', "{$item->name} updated for every country that sells it.");
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function categories(): array
    {
        return DB::table('fertilizer_categories')->orderBy('sort_order')->get(['value', 'label'])
            ->map(fn ($c) => ['value' => (string) $c->value, 'label' => (string) $c->label])->all();
    }

    public function store(FertilizerTypeRequest $request): RedirectResponse
    {
        $type = $this->types->create($request->validated());

        return redirect()->route('admin.fertilizers.create')
            ->with('success', "{$type->name} added as {$type->type}. Now choose the countries that sell it.");
    }
}
