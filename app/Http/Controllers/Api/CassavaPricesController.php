<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CassavaPriceRequest;
use App\Http\Resources\CassavaPriceResource;
use App\Http\Resources\Collections\CassavaPriceResourceCollection;
use App\Repositories\CassavaPriceRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CassavaPricesController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected CassavaPriceRepo $repo) {}

    /**
     * List Cassava Prices
     *
     * Retrieves a paginated list of cassava prices.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): CassavaPriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $cassavaPrices = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
        );

        return CassavaPriceResourceCollection::make($cassavaPrices, $this->repo);
    }

    /**
     * Cassava Prices by Country
     *
     * Retrieves a paginated list of cassava prices for a specific country.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function byCountry(string $countryCode, Request $request): CassavaPriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $filters = [
            'country' => strtoupper(trim($countryCode)),
        ];
        $cassavaPrices = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters,
        );

        return CassavaPriceResourceCollection::make($cassavaPrices, $this->repo);
    }

    public function store(CassavaPriceRequest $request): JsonResponse
    {
        $price = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new CassavaPriceResource(resource: $price),
            'message' => 'Cassava price created.',
        ], 201);
    }

    public function update(CassavaPriceRequest $request, int $id): JsonResponse
    {
        $price = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new CassavaPriceResource(resource: $price),
            'message' => 'Cassava price updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
