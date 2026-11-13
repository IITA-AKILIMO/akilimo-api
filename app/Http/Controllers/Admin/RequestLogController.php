<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Repositories\ApiRequestRepo;
use App\Service\RecommendationReportService;
use App\Service\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class RequestLogController extends Controller
{
    public function __construct(protected ApiRequestRepo $repo) {}

    public function index(Request $request): Response
    {
        $perPage = min((int)$request->get('per_page', 25), 100);
        $orderBy = $request->get('sort_by', 'created_at');
        $direction = $request->get('sort_dir', 'desc');

        $filters = [
            'country' => (string)$request->get('country', ''),
            'use_case' => (string)$request->get('use_case', ''),
            'excluded' => $request->get('excluded', ''),
            'date_from' => (string)$request->get('date_from', ''),
            'date_to' => (string)$request->get('date_to', ''),
            'search' => (string)$request->get('search', ''),
        ];

        $paginator = $this->repo->paginate($perPage, $orderBy, $direction, $filters);

        return Inertia::render('Requests/Index', [
            'requests' => [
                'data' => collect($paginator->items())->map(fn ($r) => [
                    'id' => $r->id,
                    'request_id' => $r->request_id,
                    'device_token' => $r->device_token ? substr($r->device_token, 0, 8) . '****' : null,
                    'country_code' => $r->country_code,
                    'use_case' => $r->use_case,
                    'excluded' => (bool)$r->excluded,
                    'duration_ms' => $r->request_duration_ms,
                    'created_at' => $r->created_at?->toIso8601String(),
                ])->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'filters' => array_merge($filters, ['sort_by' => $orderBy, 'sort_dir' => $direction]),
            'use_cases' => $this->repo->useCases(),
        ]);
    }

    /**
     * Make the request's PDF report again from the payload that was sent then (admin only), e.g. after the
     * report template improved or for a request made before reports were kept.
     */
    public function regenerateReport(int $id, RecommendationService $recommendations): RedirectResponse
    {
        $r = $this->repo->findOrFail($id);
        abort_if(!$r->request_id || !is_array($plan = json_decode(json_encode($r->plumber_request), true)) || $plan === [], 422,
            'This request has no stored payload to make a report from.');

        try {
            $links = $recommendations->regenerateReport($r->request_id, $plan);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'The compute service could not make the report: ' . $e->getMessage());
        }

        return back()->with($links ? 'success' : 'error', $links ? 'The PDF report was made again.' : 'The compute service returned no report for this request.');
    }

    public function show(int $id, RecommendationReportService $reports): Response
    {
        $r = $this->repo->findOrFail($id);

        return Inertia::render('Requests/Show', [
            'request' => [
                'id' => $r->id,
                'request_id' => $r->request_id,
                'device_token' => $r->device_token,
                'country_code' => $r->country_code,
                'full_names' => $r->full_names,
                'phone_number' => $r->phone_number,
                'gender_code' => $r->gender_code,
                'use_case' => $r->use_case,
                'excluded' => (bool)$r->excluded,
                'duration_ms' => $r->request_duration_ms,
                'created_at' => $r->created_at?->toIso8601String(),
                'droid_request' => $r->droid_request,
                'plumber_request' => $r->plumber_request,
                'plumber_response' => $r->plumber_response,
                // Fresh download links of the PDF report kept for this request, or null
                'report' => $r->request_id ? $reports->links($r->request_id) : null,
            ],
        ]);
    }
}
