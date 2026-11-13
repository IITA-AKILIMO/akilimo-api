<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepo;
use App\Traits\HasPaginationParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected UserRepo $repo) {}

    public function index(Request $request): ResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['name', 'username', 'email', 'created_at'], 'created_at');
        $sort = $this->getSortDirection($request);

        $users = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
        );

        return UserResource::collection($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->repo->findOrFail($id);

        return response()->json(['data' => new UserResource($user)]);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = $this->repo->create($data);

        /**
         * @status 201
         */
        return response()->json([
            'data' => new UserResource($user),
            'message' => 'User created.',
        ], 201);
    }

    public function update(UserRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->repo->update($id, $data);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'User updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
