<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PotatoPriceRequest;
use App\Http\Resources\Collections\PotatoPriceResourceCollection;
use App\Http\Resources\PotatoPriceResource;
use App\Repositories\PotatoPriceRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PotatoPricesController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected PotatoPriceRepo $repo) {}

    /**
     * List Potato Prices
     *
     * Retrieves a paginated list of potato prices.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): PotatoPriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        return PotatoPriceResourceCollection::make(
            $this->repo->paginateWithSort(perPage: $perPage, orderBy: $orderBy, direction: $sort)
        );
    }

    /**
     * Potato Prices by Country
     *
     * Retrieves a paginated list of potato prices for a specific country.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function byCountry(string $countryCode, Request $request): PotatoPriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        return PotatoPriceResourceCollection::make(
            $this->repo->paginateWithSort(
                perPage: $perPage,
                orderBy: $orderBy,
                direction: $sort,
                filters: ['country' => strtoupper(trim($countryCode))],
            )
        );
    }

    public function store(PotatoPriceRequest $request): JsonResponse
    {
        $price = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new PotatoPriceResource($price),
            'message' => 'Potato price created.',
        ], 201);
    }

    public function update(PotatoPriceRequest $request, int $id): JsonResponse
    {
        $price = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new PotatoPriceResource($price),
            'message' => 'Potato price updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
