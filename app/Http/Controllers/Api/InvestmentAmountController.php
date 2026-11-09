<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvestmentAmountRequest;
use App\Http\Resources\Collections\InvestmentAmountResourceCollection;
use App\Http\Resources\InvestmentAmountResource;
use App\Repositories\InvestmentRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestmentAmountController extends Controller
{
    use HasPaginationParams;

    public function __construct(
        protected InvestmentRepo $repo,
    ) {}

    /**
     * List Investment Amounts
     *
     * Retrieves a paginated list of investment amounts.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): InvestmentAmountResourceCollection
    {
        return $this->getPaginatedPrices($request);
    }

    /**
     * Investment Amounts by Country
     *
     * Retrieves a paginated list of active investment amounts for a specific country.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function byCountry(string $countryCode, Request $request): InvestmentAmountResourceCollection
    {
        $filters = [
            'country' => strtoupper(trim($countryCode)),
            'price_active' => true,
        ];

        return $this->getPaginatedPrices($request, $filters);
    }

    public function store(InvestmentAmountRequest $request): JsonResponse
    {
        $amount = $this->repo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new InvestmentAmountResource($amount),
            'message' => 'Investment amount created.',
        ], 201);
    }

    public function update(InvestmentAmountRequest $request, int $id): JsonResponse
    {
        $amount = $this->repo->update($id, $request->validated());

        return response()->json([
            'data' => new InvestmentAmountResource($amount),
            'message' => 'Investment amount updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json(null, 204);
    }

    private function getPaginatedPrices(Request $request, array $filters = []): InvestmentAmountResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $fertilizerPrices = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters
        );

        return InvestmentAmountResourceCollection::make($fertilizerPrices);
    }
}
