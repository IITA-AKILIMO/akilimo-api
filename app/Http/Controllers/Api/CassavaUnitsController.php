<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CassavaUnitRequest;
use App\Http\Resources\CassavaUnitResource;
use App\Http\Resources\Collections\CassavaUnitResourceCollection;
use App\Repositories\CassavaUnitRepo;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CassavaUnitsController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected CassavaUnitRepo $cassavaUnitRepo) {}

    /**
     * List Cassava Units
     *
     * Retrieves a paginated list of cassava measurement units.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    #[QueryParameter(name: 'sort', description: 'Field to sort by (sort_order, created_at).', type: 'string')]
    #[QueryParameter(name: 'order', description: 'Sort direction (asc or desc).', type: 'string')]
    public function index(Request $request): CassavaUnitResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['sort_order', 'created_at'], 'sort_order');
        $sort = $this->getSortDirection($request);

        $cassavaUnits = $this->cassavaUnitRepo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
        );


        return CassavaUnitResourceCollection::make($cassavaUnits);
    }

    public function store(CassavaUnitRequest $request): JsonResponse
    {
        $unit = $this->cassavaUnitRepo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new CassavaUnitResource($unit),
            'message' => 'Cassava unit created.',
        ], 201);
    }

    public function update(CassavaUnitRequest $request, int $id): JsonResponse
    {
        $unit = $this->cassavaUnitRepo->update($id, $request->validated());

        return response()->json([
            'data' => new CassavaUnitResource($unit),
            'message' => 'Cassava unit updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->cassavaUnitRepo->delete($id);

        return response()->json(null, 204);
    }
}
