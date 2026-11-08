<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StarchFactoryRequest;
use App\Http\Resources\Collections\StarchFactoryResourceCollection;
use App\Http\Resources\StarchFactoryResource;
use App\Repositories\StarchFactoryRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StarchFactoryController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected StarchFactoryRepo $repo) {}

    /**
     * List Starch Factories
     *
     * Retrieves a paginated list of starch factories.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, name, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): StarchFactoryResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'name', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $starchFactory = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort);

        return StarchFactoryResourceCollection::make($starchFactory);
    }

    /**
     * Starch Factories by Country
     *
     * Retrieves a paginated list of starch factories in a specific country.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, name, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function byCountry(string $countryCode, Request $request): StarchFactoryResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'name', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $filters = [
            'country' => strtoupper(trim($countryCode)),
            'factory_active' => true,
        ];

        $starchFactory = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters);

        return StarchFactoryResourceCollection::make($starchFactory);
    }

    public function store(StarchFactoryRequest $request): JsonResponse
    {
        $factory = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new StarchFactoryResource($factory),
            'message' => 'Starch factory created.',
        ], 201);
    }

    public function update(StarchFactoryRequest $request, int $id): JsonResponse
    {
        $factory = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new StarchFactoryResource($factory),
            'message' => 'Starch factory updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
