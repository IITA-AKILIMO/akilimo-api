<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\EnumUserRole;
use App\Http\Requests\Admin\UserWebRequest;
use App\Models\Country;
use App\Models\User;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(protected UserRepo $repo) {}

    public function index(Request $request): Response
    {
        $perPage = min((int)$request->get('per_page', 20), 100);
        $orderBy = in_array($request->get('sort_by'), ['name', 'username', 'email', 'created_at'])
            ? $request->get('sort_by')
            : 'created_at';
        $direction = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        $search = trim((string)$request->get('search', ''));
        $role = EnumUserRole::tryFrom((string)$request->get('role', ''))?->value ?? '';

        $paginator = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $direction,
            filters: ['role' => $role],
            search: $search,
            searchColumns: ['name', 'username', 'email'],
        );

        return Inertia::render('Users/Index', [
            'users' => [
                'data' => collect($paginator->items())->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'role' => ($u->role ?? EnumUserRole::User)->value,
                    'created_at' => $u->created_at?->toIso8601String(),
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
                'role' => $role,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'countries' => $this->countryOptions(),
        ]);
    }

    public function store(UserWebRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $reviewerCountries = $data['reviewer_countries'] ?? [];
        unset($data['reviewer_countries']);

        $data['password'] = Hash::make($data['password']);
        unset($data['password_confirmation']);

        /** @var User $created */
        $created = $this->repo->create($data);
        $created->countriesReviewed()->sync($reviewerCountries);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(int $user): Response
    {
        /** @var User $found */
        $found = $this->repo->findOrFail($user);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $found->id,
                'name' => $found->name,
                'username' => $found->username,
                'email' => $found->email,
                'role' => ($found->role ?? EnumUserRole::User)->value,
                'reviewer_countries' => $found->countriesReviewed()->pluck('countries.code')->all(),
            ],
            'countries' => $this->countryOptions(),
        ]);
    }

    public function update(UserWebRequest $request, int $user): RedirectResponse
    {
        $data = $request->validated();
        $reviewerCountries = $data['reviewer_countries'] ?? null;
        unset($data['reviewer_countries']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        /** @var User|null $updated */
        $updated = $this->repo->update($user, $data);
        if ($updated !== null && $reviewerCountries !== null) {
            $updated->countriesReviewed()->sync($reviewerCountries);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    private function countryOptions(): array
    {
        return Country::query()
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (Country $c) => ['code' => $c->code, 'name' => $c->name])
            ->all();
    }

    public function destroy(int $user): RedirectResponse
    {
        $this->repo->delete($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
