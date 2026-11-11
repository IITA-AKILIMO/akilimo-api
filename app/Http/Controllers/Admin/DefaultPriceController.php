<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\PriceStatus;
use App\Http\Requests\Admin\DefaultPriceRequest;
use App\Models\PriceCatalogue;
use App\Models\User;
use App\Repositories\PriceCatalogueRepo;
use App\Service\PriceCatalogueService;
use App\Service\PriceLookupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DefaultPriceController extends AdminController
{
    public function __construct(
        protected PriceCatalogueRepo $repo,
        protected PriceCatalogueService $catalogue,
        protected PriceLookupService $lookups,
    ) {}

    public function index(Request $request): Response
    {
        $values = $this->filterValues($request, ['search', 'country', 'status', 'category']);
        if ($values['status'] !== '' && !in_array($values['status'], PriceStatus::values(), true)) {
            $values['status'] = '';
        }

        $paginator = $this->repo->paginateWithSort(
            perPage: (int)$request->get('per_page', 20),
            orderBy: 'country',
            direction: 'asc',
            filters: [
                'country' => $values['country'],
                'status' => $values['status'],
            ],
            search: $values['search'],
            searchColumns: ['item', 'unit', 'currency', 'source'],
            // A price's category is its item's (price_items.category)
            scope: $values['category'] === '' ? null : function ($query) use ($values): void {
                $query->whereIn('item', DB::table('price_items')->where('category', $values['category'])->select('item'));
            },
        );

        // The database matches item names without regard to case, so the lookup does too
        $categoryOf = DB::table('price_items')->pluck('category', 'item')->mapWithKeys(fn ($c, $i) => [strtolower((string) $i) => (string) $c]);

        return Inertia::render('DefaultPrices/Index', [
            'filters' => $values,
            'categories' => DB::table('price_categories')->orderBy('sort_order')->get(['value', 'label'])
                ->map(fn ($c) => ['value' => (string) $c->value, 'label' => (string) $c->label])->all(),
            'items' => $this->paginateShape($paginator, fn (PriceCatalogue $f) => [
                'id' => $f->id,
                'country' => $f->country,
                'region' => $f->region,
                'item' => $f->item,
                'category' => $categoryOf[strtolower((string) $f->item)] ?? 'OTHER',
                'price' => $f->price,
                'subsidised_price' => $f->subsidised_price,
                'unit' => $f->unit,
                'bag_kg' => $f->bag_kg,
                'currency' => $f->currency,
                'min_price' => $f->min_price,
                'max_price' => $f->max_price,
                'status' => $f->status?->value,
                'source' => $f->source,
                'source_date' => $f->source_date?->toDateString(),
                'confidence' => $f->confidence,
                'effective_from' => $f->effective_from?->toDateString(),
                'submitted_by' => $f->submitted_by,
                'updated_at' => $f->updated_at?->toDateTimeString(),
            ]),
            'currentUserId' => $request->user()?->id,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DefaultPrices/Create', [
            'statuses' => PriceStatus::values(),
            'confidenceLevels' => config('price_catalogue.confidence_levels'),
            'defaultBagKg' => config('price_catalogue.default_bag_kg'),
            ...$this->lookups->forForms(),
        ]);
    }

    public function store(DefaultPriceRequest $request): RedirectResponse
    {
        $this->catalogue->createDraft($request->validated(), $this->actor($request));

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default price created as a draft. Activate it when it has been reviewed.');
    }

    public function edit(int $id): Response
    {
        /** @var PriceCatalogue $item */
        $item = $this->repo->findOrFail($id);

        return Inertia::render('DefaultPrices/Edit', [
            'item' => $this->present($item),
            'history' => $item->history()
                ->latest('id')
                ->limit(30)
                ->get()
                ->map(fn ($h) => [
                    'id' => $h->id,
                    'action' => $h->action,
                    'old_values' => $h->old_values,
                    'new_values' => $h->new_values,
                    'changed_by' => $h->changed_by,
                    'note' => $h->note,
                    'created_at' => $h->created_at?->toDateTimeString(),
                ]),
            'currentUserId' => $this->currentUserId(),
            'statuses' => PriceStatus::values(),
            'confidenceLevels' => config('price_catalogue.confidence_levels'),
            'defaultBagKg' => config('price_catalogue.default_bag_kg'),
            ...$this->lookups->forForms(),
        ]);
    }

    public function update(DefaultPriceRequest $request, int $id): RedirectResponse
    {
        /** @var PriceCatalogue $item */
        $item = $this->repo->findOrFail($id);
        $this->catalogue->saveDraft($item, $request->validated(), $this->actor($request));

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default price saved as a draft. Activate it when it has been reviewed.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        /** @var PriceCatalogue $item */
        $item = $this->repo->findOrFail($id);
        $this->catalogue->delete($item, $this->actor($request));

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default price deleted.');
    }

    public function activate(Request $request, int $id): RedirectResponse
    {
        /** @var PriceCatalogue $item */
        $item = $this->repo->findOrFail($id);
        $item = $this->catalogue->activate($item, $this->actor($request));

        return redirect()->back()->with(
            'success',
            $item->status === PriceStatus::PendingSecondReview
                ? 'Change is over 30%; a second admin must confirm it.'
                : 'Default price activated.',
        );
    }

    public function deactivate(Request $request, int $id): RedirectResponse
    {
        /** @var PriceCatalogue $item */
        $item = $this->repo->findOrFail($id);
        $this->catalogue->deactivate($item, $this->actor($request), $request->input('note'));

        return redirect()->back()->with('success', 'Default price deactivated. It is no longer served until it is activated again.');
    }

    public function confirm(Request $request, int $id): RedirectResponse
    {
        /** @var PriceCatalogue $item */
        $item = $this->repo->findOrFail($id);
        $this->catalogue->confirm($item, $this->actor($request));

        return redirect()->back()->with('success', 'Default price confirmed and activated.');
    }

    // ── Batch operations ───────────────────────────────────────────────────────

    public function batchCreate(): Response
    {
        return Inertia::render('DefaultPrices/BatchCreate', $this->lookups->forForms());
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2', 'exists:countries,code'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.item' => ['required', 'string', 'max:50', $this->lookups->itemRule()],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.unit' => ['nullable', 'string', $this->lookups->unitRule()],
            'rows.*.currency' => ['nullable', 'string', 'size:3', $this->lookups->currencyRule()],
        ]);

        $actor = $this->actor($request);

        DB::transaction(function () use ($validated, $actor) {
            foreach ($validated['rows'] as $row) {
                $this->catalogue->createDraft(array_merge($row, [
                    'country' => $validated['country'],
                    'item' => $this->lookups->canonicalItem($row['item']),
                ]), $actor);
            }
        });

        return redirect()->route('admin.default-prices.index')
            ->with('success', count($validated['rows']) . ' default price(s) created as drafts.');
    }

    public function batchEdit(Request $request): Response
    {
        $country = $request->get('country', '');

        $prices = $country
            ? $this->repo->forCountry($country)->map(fn (PriceCatalogue $p) => [
                'id' => $p->id,
                'country' => $p->country,
                'item' => $p->item,
                'price' => $p->price,
                'unit' => $p->unit,
                'currency' => $p->currency ?? '',
                'status' => $p->status?->value,
            ])->all()
            : [];

        return Inertia::render('DefaultPrices/BatchEdit', [
            'prices' => $prices,
            'country' => $country ?: null,
            ...$this->lookups->forForms(),
        ]);
    }

    public function batchUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.item' => ['required', 'string', 'max:50', $this->lookups->itemRule()],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.unit' => ['nullable', 'string', $this->lookups->unitRule()],
            'rows.*.currency' => ['nullable', 'string', 'size:3', $this->lookups->currencyRule()],
            'deleted_ids' => ['sometimes', 'array'],
            'deleted_ids.*' => ['integer'],
        ]);

        $country = $validated['country'];
        $actor = $this->actor($request);

        DB::transaction(function () use ($validated, $country, $actor) {
            if (!empty($validated['deleted_ids'])) {
                $this->repo->deleteByIds($validated['deleted_ids'], $country);
            }

            foreach ($validated['rows'] as $row) {
                $fields = [
                    'country' => $country,
                    'item' => $this->lookups->canonicalItem($row['item']),
                    'price' => $row['price'],
                    'unit' => $row['unit'] ?? null,
                    'currency' => $row['currency'] ?? null,
                ];

                if (!empty($row['id'])) {
                    /** @var PriceCatalogue $item */
                    $item = $this->repo->findOrFail($row['id']);
                    $this->catalogue->saveDraft($item, $fields, $actor);
                } else {
                    $this->catalogue->createDraft($fields, $actor);
                }
            }
        });

        return redirect()->route('admin.default-prices.index')
            ->with('success', 'Default prices saved as drafts. Activate them when they have been reviewed.');
    }

    /**
     * The shape the Edit page consumes.
     */
    private function present(PriceCatalogue $item): array
    {
        return [
            'id' => $item->id,
            'country' => $item->country,
            'region' => $item->region,
            'item' => $item->item,
            'fertilizer_type' => $item->fertilizer_type,
            'price' => $item->price,
            'subsidised_price' => $item->subsidised_price,
            'unit' => $item->unit,
            'bag_kg' => $item->bag_kg,
            'currency' => $item->currency ?? '',
            'min_price' => $item->min_price,
            'max_price' => $item->max_price,
            'status' => $item->status?->value,
            'source' => $item->source,
            'source_date' => $item->source_date?->toDateString(),
            'confidence' => $item->confidence,
            'effective_from' => $item->effective_from?->toDateString(),
            'submitted_by' => $item->submitted_by,
            'updated_by' => $item->updated_by,
        ];
    }

    private function currentUserId(): ?int
    {
        return auth()->id();
    }

    private function actor(Request $request): User
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new \RuntimeException('An authenticated user is required for this action.');
        }

        return $user;
    }
}
