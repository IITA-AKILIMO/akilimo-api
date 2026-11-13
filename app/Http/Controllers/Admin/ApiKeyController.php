<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole as UserRole;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admins manage every key with any abilities. Partners reach the same screens but only see and
 * manage their own keys, and only with the partner and MCP abilities: never a wildcard (empty)
 * key, `admin`, or a key for another user.
 */
class ApiKeyController extends BaseController
{
    public function index(Request $request): Response
    {
        $perPage = min((int)$request->get('per_page', 20), 100);
        $orderBy = in_array($request->get('sort_by'), ['name', 'key_prefix', 'is_active', 'created_at', 'last_used_at', 'expires_at'])
            ? $request->get('sort_by')
            : 'created_at';
        $direction = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        $search = (string)$request->get('search', '');
        $status = $request->get('status'); // 'active', 'inactive', 'expired'

        $query = $this->keys($request)->with('user:id,name,email')
            ->when($search !== '', fn ($q) => $q->where(fn ($sq) => $sq->where('name', 'like', "%{$search}%")
                ->orWhere('key_prefix', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true)->where(fn ($sq) => $sq->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'expired', fn ($q) => $q->whereNotNull('expires_at')->where('expires_at', '<=', now()));

        $paginator = $query->orderBy($orderBy, $direction)->paginate($perPage);

        return Inertia::render('ApiKeys/Index', [
            'apiKeys' => [
                'data' => collect($paginator->items())->map(fn ($key) => [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key_prefix' => $key->key_prefix,
                    'is_active' => $key->is_active,
                    'abilities' => $key->abilities,
                    'last_used_at' => $key->last_used_at?->toIso8601String(),
                    'expires_at' => $key->expires_at?->toIso8601String(),
                    'created_at' => $key->created_at?->toIso8601String(),
                    'user' => $key->user ? [
                        'id' => $key->user->id,
                        'name' => $key->user->name,
                        'email' => $key->user->email,
                    ] : null,
                ])->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'filters' => [
                'sort_by' => $orderBy,
                'sort_dir' => $direction,
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('ApiKeys/Create', $this->formOptions($request));
    }

    /**
     * The raw key is rendered straight into the POST response (never flashed to the
     * session or stored), so it is visible exactly once. History is encrypted so the
     * key is not readable from the browser's history state after leaving the page.
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate($this->rules($request, [
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]));

        $rawKey = 'ak_' . bin2hex(random_bytes(16));
        $prefix = substr($rawKey, 0, 12);
        $hash = hash('sha256', $rawKey);

        $apiKey = ApiKey::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'key_prefix' => $prefix,
            'key_hash' => $hash,
            'abilities' => ($validated['abilities'] ?? null) ?: null, // empty = wildcard (admins only)
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);
        $apiKey->load('user:id,name,email');

        // The client can only encrypt history with WebCrypto (HTTPS); on plain http it throws and the page never changes
        if ($request->secure()) {
            Inertia::encryptHistory();
        }

        return Inertia::render('ApiKeys/Created', [
            'apiKey' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'abilities' => $apiKey->abilities,
                'expires_at' => $apiKey->expires_at?->toIso8601String(),
                'user' => $apiKey->user ? [
                    'name' => $apiKey->user->name,
                    'email' => $apiKey->user->email,
                ] : null,
            ],
            'plainTextKey' => $rawKey,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $apiKey = $this->keys($request)->with('user:id,name,email,role')->findOrFail($id);

        return Inertia::render('ApiKeys/Edit', [
            'apiKey' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'is_active' => $apiKey->is_active,
                'abilities' => $apiKey->abilities,
                'expires_at' => $apiKey->expires_at?->toDateString(),
                'created_at' => $apiKey->created_at?->toIso8601String(),
                'user_id' => $apiKey->user_id,
                'user' => $apiKey->user ? [
                    'id' => $apiKey->user->id,
                    'name' => $apiKey->user->name,
                    'email' => $apiKey->user->email,
                    'role' => ($apiKey->user->role ?? UserRole::User)->value,
                ] : null,
            ],
            ...$this->formOptions($request),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $apiKey = $this->keys($request)->findOrFail($id);

        $validated = $request->validate($this->rules($request, [
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]));

        $apiKey->update([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'abilities' => ($validated['abilities'] ?? null) ?: null, // empty = wildcard (admins only)
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.api-keys.index')
            ->with('success', 'API key updated successfully.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->keys($request)->findOrFail($id)->delete();

        return redirect()->route('admin.api-keys.index')
            ->with('success', 'API key deleted.');
    }

    public function revoke(Request $request, int $id): RedirectResponse
    {
        $apiKey = $this->keys($request)->findOrFail($id);
        $apiKey->update(['is_active' => false]);

        return redirect()->route('admin.api-keys.index')
            ->with('success', 'API key revoked.');
    }

    public function activate(Request $request, int $id): RedirectResponse
    {
        $apiKey = $this->keys($request)->findOrFail($id);
        $apiKey->update(['is_active' => true]);

        return redirect()->route('admin.api-keys.index')
            ->with('success', 'API key activated.');
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === UserRole::Admin;
    }

    /** Keys the signed-in user may see and change: all for admins, their own for partners. */
    private function keys(Request $request): Builder
    {
        return ApiKey::query()->when(!$this->isAdmin($request), fn ($q) => $q->where('user_id', $request->user()->id));
    }

    /** @return array<int, string> */
    private function grantableAbilities(Request $request): array
    {
        return $this->isAdmin($request)
            ? TokenAbility::ALL
            : array_values(array_unique([...TokenAbility::PARTNER_ABILITIES, ...TokenAbility::MCP_ABILITIES]));
    }

    /**
     * Validation shared by store and update. Partners must pick at least one ability (an empty
     * list means wildcard) and can only name themselves as the owner.
     */
    private function rules(Request $request, array $extra): array
    {
        $admin = $this->isAdmin($request);

        return [
            'user_id' => $admin
                ? ['required', 'integer', Rule::exists('users', 'id')]
                : ['required', 'integer', Rule::in([$request->user()->id])],
            'name' => ['required', 'string', 'max:100'],
            'abilities' => $admin ? ['nullable', 'array'] : ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in($this->grantableAbilities($request))],
            ...$extra,
        ];
    }

    /** Owners, abilities and presets the create and edit forms offer to the signed-in user. */
    private function formOptions(Request $request): array
    {
        $admin = $this->isAdmin($request);
        $grantable = $this->grantableAbilities($request);
        $users = $admin
            ? User::orderBy('name')->get(['id', 'name', 'email', 'role'])
            : collect([$request->user()]);

        return [
            'users' => $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => ($u->role ?? UserRole::User)->value,
            ])->values()->all(),
            'abilities' => $grantable,
            'abilityGroups' => array_filter(
                array_map(fn ($group) => array_values(array_intersect($group, $grantable)), TokenAbility::GROUPS),
            ),
            'abilityLabels' => TokenAbility::LABELS,
            'rolePresets' => $admin
                ? [
                    UserRole::User->value => TokenAbility::PLAYGROUND_ABILITIES,
                    UserRole::Partner->value => TokenAbility::PARTNER_ABILITIES,
                    UserRole::Admin->value => [TokenAbility::WILDCARD],
                ]
                : [UserRole::Partner->value => TokenAbility::PARTNER_ABILITIES],
            'canWildcard' => $admin,
        ];
    }
}
