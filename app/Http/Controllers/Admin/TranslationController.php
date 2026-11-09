<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TranslationRequest;
use App\Repositories\TranslationRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TranslationController extends AdminController
{
    public function __construct(protected TranslationRepo $repo) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'missing']);
        // Rows still to translate: that language's text is empty
        $missing = in_array($values['missing'], ['sw', 'rw'], true) ? $values['missing'] : null;

        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'key',
            direction: 'asc',
            // Was the key only; the text in any language now finds a row too
            search: $values['search'],
            searchColumns: ['key', 'en', 'sw', 'rw'],
            scope: $missing ? fn ($q) => $q->where(fn ($w) => $w->whereNull($missing)->orWhere($missing, '')) : null,
        );

        return Inertia::render('Translations/Index', [
            'filters' => $values,
            'items' => $this->paginateShape($paginator, fn ($f) => [
                'id' => $f->id,
                'key' => $f->key,
                'en' => $f->en,
                'sw' => $f->sw,
                'rw' => $f->rw,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Translations/Create');
    }

    public function store(TranslationRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return redirect()->route('admin.translations.index')
            ->with('success', 'Translation created.');
    }

    public function edit(int $id): Response
    {
        $item = $this->repo->findOrFail($id);

        return Inertia::render('Translations/Edit', ['item' => $item->toArray()]);
    }

    public function update(TranslationRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return redirect()->route('admin.translations.index')
            ->with('success', 'Translation updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return redirect()->route('admin.translations.index')
            ->with('success', 'Translation deleted.');
    }

    // ── Batch operations ───────────────────────────────────────────────────────

    public function batchEdit(Request $request): Response
    {
        $search = (string)$request->get('search', '');
        $limit = (int)$request->get('limit', 50);

        $rows = $this->repo->forBatchEdit($search, $limit)->map(fn ($t) => [
            'id' => $t->id,
            'key' => $t->key,
            'en' => $t->en ?? '',
            'sw' => $t->sw ?? '',
            'rw' => $t->rw ?? '',
        ])->all();

        return Inertia::render('Translations/BatchEdit', [
            'rows' => $rows,
            'search' => $search,
            'limit' => $limit,
        ]);
    }

    public function batchUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.key' => ['required', 'string', 'max:255'],
            'rows.*.en' => ['required', 'string'],
            'rows.*.sw' => ['nullable', 'string'],
            'rows.*.rw' => ['nullable', 'string'],
            'deleted_ids' => ['sometimes', 'array'],
            'deleted_ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($validated) {
            if (!empty($validated['deleted_ids'])) {
                $this->repo->deleteByIds($validated['deleted_ids']);
            }

            foreach ($validated['rows'] as $row) {
                $fields = [
                    'key' => $row['key'],
                    'en' => $row['en'],
                    'sw' => $row['sw'] ?? null,
                    'rw' => $row['rw'] ?? null,
                ];

                if (!empty($row['id'])) {
                    $this->repo->update($row['id'], $fields);
                } else {
                    $this->repo->create($fields);
                }
            }
        });

        return redirect()->route('admin.translations.index')
            ->with('success', 'Translations updated.');
    }
}
