<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OperationCostRequest;
use App\Http\Resources\Collections\OperationCostResourceCollection;
use App\Http\Resources\OperationCostResource;
use App\Repositories\OperationCostRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationCostController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected OperationCostRepo $repo) {}

    /**
     * List Operation Costs
     *
     * Retrieves a paginated list of operation costs. Optionally filter by operation name and type.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, max_cost, min_cost, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    #[QueryParameter(name: 'operation_name', description: 'Filter by operation name.', type: 'string')]
    #[QueryParameter(name: 'operation_type', description: 'Filter by operation type.', type: 'string')]
    public function index(Request $request): OperationCostResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'max_cost', 'min_cost', 'created_at'], 'max_cost');
        $sort = $this->getSortDirection($request);
        $operationName = $request->input('operation_name');
        $operationType = $request->input('operation_type');

        $filters = array_filter([
            'operation_name' => filled($operationName) ? strtolower(trim($operationName)) : null,
            'operation_type' => filled($operationType) ? strtolower(trim($operationType)) : null,
        ]);

        $operationCosts = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters);

        return OperationCostResourceCollection::make($operationCosts);
    }

    /**
     * Operation Costs by Country
     *
     * Retrieves a paginated list of operation costs for a specific country. Optionally filter by operation name and type.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, max_cost, min_cost, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    #[QueryParameter(name: 'operation_name', description: 'Filter by operation name.', type: 'string')]
    #[QueryParameter(name: 'operation_type', description: 'Filter by operation type.', type: 'string')]
    public function byCountry(string $countryCode, Request $request): OperationCostResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'max_cost', 'min_cost', 'created_at'], 'max_cost');
        $sort = $this->getSortDirection($request);
        $operationName = $request->input('operation_name');
        $operationType = $request->input('operation_type');

        $filters = array_filter([
            'country_code' => $countryCode,
            'operation_name' => filled($operationName) ? strtolower(trim($operationName)) : null,
            'operation_type' => filled($operationType) ? strtolower(trim($operationType)) : null,
        ]);

        $operationCosts = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters);

        return OperationCostResourceCollection::make($operationCosts);
    }

    public function store(OperationCostRequest $request): JsonResponse
    {
        $cost = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new OperationCostResource($cost),
            'message' => 'Operation cost created.',
        ], 201);
    }

    public function update(OperationCostRequest $request, int $id): JsonResponse
    {
        $cost = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new OperationCostResource($cost),
            'message' => 'Operation cost updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }
}
