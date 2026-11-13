<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Service\SettingsPreviewService;
use App\Support\ReferenceTables;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One set of screens for the small reference and settings tables listed in
 * ReferenceTables: what the price forms choose from, and the rules the app's
 * price lists are computed with. A row is addressed by its key columns in the
 * query string (?key[value]=per_bag), so tables with a composite key work too.
 */
class ReferenceDataController extends AdminController
{
    public function hub(): Response
    {
        // Lists that sit under Catalogue (fertilizers, units of sale) are reached from there, as tabs
        $tables = collect(ReferenceTables::all())->reject(fn (array $def) => isset($def['area']))->map(fn (array $def, string $slug) => [
            'slug' => $slug,
            'title' => $def['title'],
            'description' => $def['description'],
            'rows' => DB::table($def['table'])->count(),
        ])->values();

        return Inertia::render('Reference/Hub', ['tables' => $tables]);
    }

    public function index(Request $request, string $slug): Response
    {
        $def = $this->definition($slug);
        $keys = $this->keyNames($def);

        $textColumns = collect($def['fields'])->whereIn('type', ['text', 'select'])->pluck('name')->all();
        $selects = collect($def['fields'])->where('type', 'select')->pluck('name')->all();

        $values = $this->filterValues($request, ['search', ...$selects]);

        $query = DB::table($def['table']);

        if ($values['search'] !== '' && $textColumns !== []) {
            $term = '%' . addcslashes($values['search'], '%_\\') . '%';
            $query->where(function ($q) use ($textColumns, $term) {
                foreach ($textColumns as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }

        foreach ($selects as $name) {
            if ($values[$name] !== '') {
                $query->where($name, $values[$name]);
            }
        }

        foreach ($def['order'] as $column) {
            $query->orderBy($column);
        }

        $paginator = $query->paginate((int) min(100, max(10, (int) $request->get('per_page', 20))))->withQueryString();

        return Inertia::render('Reference/Index', [
            'resource' => $this->present($def),
            'filters' => $values,
            // A filter offers the values the table actually holds
            'selectFilters' => collect($selects)->map(function (string $name) use ($def) {
                $field = collect($def['fields'])->firstWhere('name', $name);

                return [
                    'key' => $name,
                    'label' => $field['label'],
                    'options' => DB::table($def['table'])->whereNotNull($name)->distinct()->orderBy($name)->pluck($name)
                        ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])->all(),
                ];
            })->values()->all(),
            'items' => $this->paginateShape($paginator, function ($row) use ($def, $keys) {
                $values = [];
                foreach ($def['fields'] as $field) {
                    $values[$field['name']] = $row->{$field['name']};
                }

                return $values + ['_key' => collect($keys)->mapWithKeys(fn ($k) => [$k => $row->{$k}])->all()];
            }),
        ]);
    }

    public function create(string $slug): Response
    {
        $def = $this->definition($slug);
        abort_if(isset($def['create_url']), 404);

        return Inertia::render('Reference/Form', [
            'resource' => $this->present($def),
            'row' => null,
            'options' => $this->options($def),
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $def = $this->definition($slug);
        abort_if(isset($def['create_url']), 404);

        $data = $request->validate($this->rules($def, includeKeys: true));
        $this->assertKeyFree($def, $data);

        DB::table($def['table'])->insert($data + $this->timestamps($def, creating: true));

        return redirect()->route('admin.reference.index', $slug)->with('success', 'Added.');
    }

    /**
     * What saving this form would do to the app's price lists, without saving it.
     * Takes the same input as the form and answers with the lists' before and after.
     */
    public function preview(Request $request, string $slug, SettingsPreviewService $preview): JsonResponse
    {
        $def = $this->definition($slug);
        abort_if(empty($def['previews']), 404);

        if ($request->boolean('delete')) {
            // What removing the row would do; a row that others still use cannot be removed
            $key = $this->keyFromRequest($def, $request);
            $this->row($def, $key);

            try {
                return response()->json($preview->compare($def['previews'], fn () => DB::table($def['table'])->where($key)->delete()));
            } catch (QueryException) {
                return response()->json(['blocked' => true, 'message' => 'This row is still used by other data, so it cannot be deleted.']);
            }
        }

        if ($request->query('key') !== null) {
            $key = $this->keyFromRequest($def, $request);
            $this->row($def, $key);
            $data = $request->validate($this->rules($def, includeKeys: false));
            $change = fn () => DB::table($def['table'])->where($key)->update($data + $this->timestamps($def, creating: false));
        } else {
            $data = $request->validate($this->rules($def, includeKeys: true));
            $this->assertKeyFree($def, $data);
            $change = fn () => DB::table($def['table'])->insert($data + $this->timestamps($def, creating: true));
        }

        return response()->json($preview->compare($def['previews'], $change));
    }

    public function edit(Request $request, string $slug): Response
    {
        $def = $this->definition($slug);
        $key = $this->keyFromRequest($def, $request);
        $row = $this->row($def, $key);

        return Inertia::render('Reference/Form', [
            'resource' => $this->present($def),
            'row' => collect($def['fields'])->mapWithKeys(fn ($f) => [$f['name'] => $row->{$f['name']}])->all(),
            'rowKey' => $key,
            'options' => $this->options($def),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $def = $this->definition($slug);
        $key = $this->keyFromRequest($def, $request);
        $this->row($def, $key);

        $data = $request->validate($this->rules($def, includeKeys: false));

        if ($data !== []) {
            DB::table($def['table'])->where($key)->update($data + $this->timestamps($def, creating: false));
        }

        return redirect()->route('admin.reference.index', $slug)->with('success', 'Saved.');
    }

    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $def = $this->definition($slug);
        $key = $this->keyFromRequest($def, $request);
        $this->row($def, $key);

        try {
            DB::table($def['table'])->where($key)->delete();
        } catch (QueryException) {
            // A row that other data still points to (an item with prices, a unit in use)
            return redirect()->route('admin.reference.index', $slug)
                ->with('error', 'This row is still used by other data, so it cannot be deleted.');
        }

        return redirect()->route('admin.reference.index', $slug)->with('success', 'Deleted.');
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function definition(string $slug): array
    {
        return ReferenceTables::find($slug) ?? abort(404);
    }

    /**
     * A new row must not repeat the key of an existing one.
     *
     * @param array<string, mixed> $def
     * @param array<string, mixed> $data
     */
    private function assertKeyFree(array $def, array $data): void
    {
        $keys = collect($this->keyNames($def))->mapWithKeys(fn ($k) => [$k => $data[$k]])->all();

        if (DB::table($def['table'])->where($keys)->exists()) {
            throw ValidationException::withMessages([
                $this->keyNames($def)[0] => 'A row with this ' . implode(' and ', $this->keyNames($def)) . ' already exists.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $def
     * @return list<string>
     */
    private function keyNames(array $def): array
    {
        return collect($def['fields'])->filter(fn ($f) => $f['key'] ?? false)->pluck('name')->values()->all();
    }

    /**
     * The key columns of the row being edited, from ?key[column]=value.
     *
     * @param array<string, mixed> $def
     * @return array<string, string>
     */
    private function keyFromRequest(array $def, Request $request): array
    {
        $given = (array) $request->query('key', []);
        $key = [];

        foreach ($this->keyNames($def) as $name) {
            abort_unless(isset($given[$name]) && is_scalar($given[$name]), 404);
            $key[$name] = (string) $given[$name];
        }

        return $key;
    }

    /**
     * @param array<string, mixed> $def
     * @param array<string, string> $key
     */
    private function row(array $def, array $key): object
    {
        return DB::table($def['table'])->where($key)->first() ?? abort(404);
    }

    /**
     * Validation rules for the form: every field when adding, the non-key fields
     * when editing (the key is read-only). A select must name a row in its table.
     *
     * @param array<string, mixed> $def
     * @return array<string, list<mixed>>
     */
    private function rules(array $def, bool $includeKeys): array
    {
        $rules = [];

        foreach ($def['fields'] as $field) {
            if (($field['key'] ?? false) && ! $includeKeys) {
                continue;
            }

            $fieldRules = $field['rules'];

            if ($field['type'] === 'select') {
                $fieldRules[] = Rule::exists($field['from']['table'], $field['from']['value']);
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @param array<string, mixed> $def
     * @return array<string, mixed>
     */
    private function timestamps(array $def, bool $creating): array
    {
        if (! ($def['timestamps'] ?? false)) {
            return [];
        }

        return $creating ? ['created_at' => now(), 'updated_at' => now()] : ['updated_at' => now()];
    }

    /**
     * What a screen needs to know about a table, without the validation rules.
     *
     * @param array<string, mixed> $def
     * @return array<string, mixed>
     */
    private function present(array $def): array
    {
        return [
            'slug' => $def['slug'],
            'title' => $def['title'],
            'description' => $def['description'],
            'warning' => $def['warning'] ?? null,
            'createUrl' => $def['create_url'] ?? null,
            'canPreview' => ! empty($def['previews']),
            'fields' => collect($def['fields'])->map(fn ($f) => [
                'name' => $f['name'],
                'label' => $f['label'],
                'type' => $f['type'],
                'key' => (bool) ($f['key'] ?? false),
                'required' => in_array('required', $f['rules'], true),
                'help' => $f['help'] ?? null,
                'step' => $f['step'] ?? null,
                'default' => $f['default'] ?? null,
            ])->all(),
        ];
    }

    /**
     * The choices of each select field, from the table it points to.
     *
     * @param array<string, mixed> $def
     * @return array<string, list<array{value: string, label: string}>>
     */
    private function options(array $def): array
    {
        $options = [];

        foreach ($def['fields'] as $field) {
            if ($field['type'] !== 'select') {
                continue;
            }

            $from = $field['from'];
            $labelColumn = $from['label'] ?? $from['value'];

            $options[$field['name']] = DB::table($from['table'])
                ->select($from['value'], $labelColumn)
                ->distinct()
                ->orderBy($from['value'])
                ->get()
                ->map(fn ($o) => [
                    'value' => (string) $o->{$from['value']},
                    'label' => $labelColumn === $from['value'] ? (string) $o->{$from['value']} : $o->{$from['value']} . ' — ' . $o->{$labelColumn},
                ])
                ->all();
        }

        return $options;
    }
}
