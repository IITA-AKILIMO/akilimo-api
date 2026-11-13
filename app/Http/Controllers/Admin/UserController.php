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
use App\Repositories\CountryRepo;
use App\Repositories\UserRepo;
use App\Traits\HasPaginationParams;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected UserRepo $userRepo, protected CountryRepo $countryRepo)
    {
    }

    public function index(Request $request): Response
    {
        $perPage = $this->getPerPage($request, 20);
        $orderBy = $this->getOrderBy($request, ['name', 'username', 'email', 'created_at'], 'created_at');
        $direction = $this->getSortDirection($request);


        $search = trim((string)$request->input('search', ''));
        $role = EnumUserRole::tryFrom((string)$request->input('role', ''))?->value ?? '';

        $paginator = $this->userRepo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $direction,
            filters: ['role' => $role],
            search: $search,
            searchColumns: ['name', 'username', 'email'],
        );

        return Inertia::render('Users/Index', [
            'users' => [
                'data' => collect($paginator->items())->map(fn($u) => [
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
            'countries' => $this->countryRepo->countryOptions(),
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
        $created = $this->userRepo->create($data);
        $created->countriesReviewed()->sync($reviewerCountries);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(int $user): Response
    {
        /** @var User $found */
        $found = $this->userRepo->findOrFail($user);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $found->id,
                'name' => $found->name,
                'username' => $found->username,
                'email' => $found->email,
                'role' => ($found->role ?? EnumUserRole::User)->value,
                'reviewer_countries' => $found->countriesReviewed()->pluck('countries.code')->all(),
            ],
            'countries' => $this->countryRepo->countryOptions(),
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
        $updated = $this->userRepo->update($user, $data);
        if ($updated !== null && $reviewerCountries !== null) {
            $updated->countriesReviewed()->sync($reviewerCountries);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(int $user): RedirectResponse
    {
        $this->userRepo->delete($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
