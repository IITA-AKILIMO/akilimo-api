<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Enums\PriceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PriceCatalogueRequest;
use App\Http\Resources\Collections\PriceCatalogueResourceCollection;
use App\Http\Resources\PriceCatalogueResource;
use App\Models\PriceCatalogue;
use App\Models\User;
use App\Repositories\PriceCatalogueRepo;
use App\Service\PriceCatalogueService;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceCatalogueController extends Controller
{
    use HasPaginationParams;

    public function __construct(
        protected PriceCatalogueRepo $repo,
        protected PriceCatalogueService $catalogue,
    ) {}

    /**
     * List Price Catalogue
     *
     * Retrieves a paginated list of the price catalogue. Optionally filter by country.
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
            // Only reviewed prices reach the engine; drafts stay in the admin.
            'status' => PriceStatus::Active->value,
        ];

        $entries = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
            filters: $filters);

        return PriceCatalogueResourceCollection::make($entries);
    }

    public function store(PriceCatalogueRequest $request): JsonResponse
    {
        $actor = $request->user();

        $price = $this->catalogue->createDraft($request->validated(), $actor instanceof User ? $actor : null);

        /**
         * @status 201
         */
        return response()->json([
            'data' => new PriceCatalogueResource($price),
            'message' => 'Catalogue price created as a draft.',
        ], 201);
    }

    public function update(PriceCatalogueRequest $request, int $id): JsonResponse
    {
        /** @var PriceCatalogue $price */
        $price = $this->repo->findOrFail($id);
        $actor = $request->user();
        $price = $this->catalogue->saveDraft($price, $request->validated(), $actor instanceof User ? $actor : null);

        return response()->json([
            'data' => new PriceCatalogueResource($price),
            'message' => 'Catalogue price updated as a draft.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var PriceCatalogue $price */
        $price = $this->repo->findOrFail($id);
        $actor = $request->user();
        $this->catalogue->delete($price, $actor instanceof User ? $actor : null);

        return response()->json(null, 204);
    }
}
