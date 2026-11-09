<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FertilizerRequest;
use App\Http\Resources\Collections\FertilizerResourceCollection;
use App\Http\Resources\FertilizerResource;
use App\Repositories\FertilizerRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FertilizerController extends Controller
{
    use HasPaginationParams;

    public function __construct(
        protected FertilizerRepo $fertilizerRepo,
    ) {
        // empty constructor
    }

    /**
     * List Fertilizers
     *
     * Retrieves a paginated list of all fertilizers.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, name, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): FertilizerResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'name', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $availableFertilizers = $this->fertilizerRepo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort);

        return FertilizerResourceCollection::make($availableFertilizers);
    }

    /**
     * Fertilizers by Country
     *
     * Retrieves a paginated list of fertilizers available in a specific country. Optionally filter by use case.
     *
     * @unauthenticated
     */
    #[PathParameter(name: 'countryCode', description: 'ISO 3166-1 alpha-2 country code (e.g. NG, TZ).')]
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, name, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    #[QueryParameter(name: 'use_case', description: 'Filter fertilizers by use case (e.g. MAIZE, CASSAVA, RICE).', type: 'string')]
    public function byCountry(string $countryCode, Request $request): FertilizerResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'name', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);
        $useCase = $request->input('use_case');

        $filters = [
            'country' => strtoupper($countryCode),
        ];

        $trimmed = Str::of($useCase)->trim();
        if ($trimmed->isNotEmpty()) {
            $filters['use_case'] = $trimmed->upper()->toString();
        }

        $availableFertilizers = $this->fertilizerRepo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters);

        return FertilizerResourceCollection::make($availableFertilizers);
    }

    public function store(FertilizerRequest $request): JsonResponse
    {
        $fertilizer = $this->fertilizerRepo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new FertilizerResource($fertilizer),
            'message' => 'Fertilizer created.',
        ], 201);
    }

    public function update(FertilizerRequest $request, int $id): JsonResponse
    {
        $fertilizer = $this->fertilizerRepo->update($id, $request->validated());

        return response()->json([
            'data' => new FertilizerResource($fertilizer),
            'message' => 'Fertilizer updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->fertilizerRepo->delete($id);

        return response()->json(null, 204);
    }
}
