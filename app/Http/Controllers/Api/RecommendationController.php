<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComputeRequest;
use App\Http\Resources\ApiRequestResourceCollection;
use App\Http\Resources\RecommendationResource;
use App\Repositories\ApiRequestRepo;
use App\Service\RecommendationReportService;
use App\Service\RecommendationService;
use App\Traits\HasPaginationParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecommendationController extends Controller
{
    use HasPaginationParams;

    public function __construct(
        protected RecommendationService $recommendationService,
        protected ApiRequestRepo $repo,
    ) {}

    public function index(Request $request): ApiRequestResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['created_at', 'updated_at', 'request_id'], 'created_at');
        $sort = $this->getSortDirection($request);

        $recommendationData = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort);

        return ApiRequestResourceCollection::make($recommendationData);
    }

    /**
     * Processes the computation of fertilizer recommendations based on the provided request data.
     *
     * Extracts user information, computation details, and fertilizer list from the request object.
     * Retrieves available fertilizers from the repository based on specific conditions (e.g., country and availability).
     * Maps the retrieved fertilizers and updates their properties (e.g., selection status, weight, and price) based on the provided fertilizer list.
     * Generates a new compute request using the user information, computation details, and processed fertilizers.
     * Adjusts the area unit in the request to standardized values for further processing.
     * Sends the request to the computation service and returns the original request data, the computed response, and the final request sent to the service.
     *
     * @param ComputeRequest $request The incoming compute request containing user data, computation details, and a list of fertilizers.
     *
     * @throws \JsonException
     */
    public function computeRecommendations(ComputeRequest $request): RecommendationResource
    {
        $result = $this->recommendationService->compute(droidRequest: $request->toArray(), userId: $request->attributes->get('farmer_id'));

        return RecommendationResource::make($result);
    }

    /**
     * Download a recommendation's PDF report. Reached only through a signed, expiring link (`report.url` in the
     * compute answer, or one from reportLinks); the signature is checked by the `signed:relative` middleware.
     */
    public function report(string $report, string $file, RecommendationReportService $reports): Response
    {
        return $reports->download($report, $file) ?? abort(404, 'This report is no longer available.');
    }

    /**
     * New download links for a stored report: an old link (in saved history) has expired, the report has not.
     * The id is report.id from the compute answer (the request_id of the computation).
     */
    public function reportLinks(string $report, RecommendationReportService $reports): JsonResponse
    {
        $links = $reports->links($report);
        abort_if($links === null, 404, 'This recommendation has no report.');

        return response()->json(['data' => $links]);
    }
}
