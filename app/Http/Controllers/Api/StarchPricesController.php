<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StarchPriceRequest;
use App\Http\Resources\Collections\StarchPriceResourceCollection;
use App\Http\Resources\StarchPriceResource;
use App\Repositories\StarchPriceRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StarchPricesController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected StarchPriceRepo $repo)
    {
    }

    /**
     * List Starch Prices
     *
     * Retrieves a paginated list of starch prices. Optionally filter by country.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    #[QueryParameter(name: 'country', description: 'Filter by ISO 3166-1 alpha-2 country code.', type: 'string')]
    public function index(Request $request): StarchPriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['created_at'], 'created_at');
        $sort = $this->getSortDirection($request);

        // Inactive factories (e.g. unconfirmed prices) stay out of the feed clients compute with
        $relationFilters = [
            'starchFactory' => ['country' => $request->input('country'), 'factory_active' => true],
        ];

        $starchPrices = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            with: ['starchFactory'],
            relationFilters: $relationFilters);

        return StarchPriceResourceCollection::make($starchPrices);
    }

    public function store(StarchPriceRequest $request): JsonResponse
    {
        $price = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new StarchPriceResource($price->load('starch_factory')),
            'message' => 'Starch price created.',
        ], 201);
    }

    public function update(StarchPriceRequest $request, int $id): JsonResponse
    {
        $price = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new StarchPriceResource($price->load('starch_factory')),
            'message' => 'Starch price updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
