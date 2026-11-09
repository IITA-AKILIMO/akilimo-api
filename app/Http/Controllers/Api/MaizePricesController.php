<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MaizePriceRequest;
use App\Http\Resources\Collections\MaizePriceResourceCollection;
use App\Http\Resources\MaizePriceResource;
use App\Repositories\MaizePriceRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaizePricesController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected MaizePriceRepo $repo) {}

    /**
     * List Maize Prices
     *
     * Retrieves a paginated list of maize prices.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): MaizePriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        return MaizePriceResourceCollection::make(
            $this->repo->paginateWithSort(perPage: $perPage, orderBy: $orderBy, direction: $sort),
            $this->repo,
        );
    }

    /**
     * Maize Prices by Country
     *
     * Retrieves a paginated list of maize prices for a specific country.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function byCountry(string $countryCode, Request $request): MaizePriceResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        return MaizePriceResourceCollection::make(
            $this->repo->paginateWithSort(
                perPage: $perPage,
                orderBy: $orderBy,
                direction: $sort,
                filters: ['country' => strtoupper(trim($countryCode))],
            ),
            $this->repo,
        );
    }

    public function store(MaizePriceRequest $request): JsonResponse
    {
        $price = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new MaizePriceResource($price),
            'message' => 'Maize price created.',
        ], 201);
    }

    public function update(MaizePriceRequest $request, int $id): JsonResponse
    {
        $price = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new MaizePriceResource($price),
            'message' => 'Maize price updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
