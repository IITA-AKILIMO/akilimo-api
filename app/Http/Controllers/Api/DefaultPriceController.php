<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DefaultPriceRequest;
use App\Http\Resources\Collections\DefaultPriceResourceCollection;
use App\Http\Resources\DefaultPriceResource;
use App\Repositories\DefaultPriceRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DefaultPriceController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected DefaultPriceRepo $repo) {}

    /**
     * List Default Prices
     *
     * Retrieves a paginated list of default prices. Optionally filter by country.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    #[QueryParameter(name: 'country', description: 'Filter by ISO 3166-1 alpha-2 country code.', type: 'string')]
    public function index(Request $request)
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['created_at'], 'created_at');
        $sort = $this->getSortDirection($request);

        $country = $request->input('country');

        $filters = [
            // Burundi was filed as "BU" before its ISO code; older clients may still ask for it
            'country' => is_string($country) && strtoupper($country) === 'BU' ? 'BI' : $country,
        ];

        $defaultPrices = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters);

        return DefaultPriceResourceCollection::make($defaultPrices);
    }

    public function store(DefaultPriceRequest $request): JsonResponse
    {
        $price = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new DefaultPriceResource($price),
            'message' => 'Default price created.',
        ], 201);
    }

    public function update(DefaultPriceRequest $request, int $id): JsonResponse
    {
        $price = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new DefaultPriceResource($price),
            'message' => 'Default price updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
