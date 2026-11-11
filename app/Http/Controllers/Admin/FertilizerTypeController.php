<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FertilizerTypeRequest;
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
        return Inertia::render('FertilizerTypes/Create', [
            'categories' => DB::table('fertilizer_categories')->orderBy('sort_order')->get(['value', 'label'])
                ->map(fn ($c) => ['value' => (string) $c->value, 'label' => (string) $c->label])->all(),
        ]);
    }

    public function store(FertilizerTypeRequest $request): RedirectResponse
    {
        $type = $this->types->create($request->validated());

        return redirect()->route('admin.fertilizers.create')
            ->with('success', "{$type->name} added as {$type->type}. Now choose the countries that sell it.");
    }
}
