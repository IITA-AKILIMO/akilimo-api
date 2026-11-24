<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Web\Playground;

use App\Exceptions\RecommendationException;
use App\Http\Requests\Playground\PlaygroundComputeRequest;
use App\Models\ApiRequest;
use App\Repositories\ApiRequestRepo;
use App\Service\Playground\DroidRequestBuilder;
use App\Service\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PlaygroundController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ApiRequestRepo $apiRequestRepo,
        private readonly DroidRequestBuilder $droidRequest,
    ) {}

    public function show(): View
    {
        return view('playground.app');
    }

    public function showHealth(): View
    {
        return view('playground.health');
    }

    public function history(Request $request): JsonResponse
    {
        $records = $this->apiRequestRepo->playgroundHistory($request->user()->id);

        $items = $records->map(fn (ApiRequest $r) => [
            'id' => $r->id,
            'request_id' => $r->request_id,
            'country_code' => $r->country_code,
            'use_case' => $r->use_case,
            'flags' => ['fr' => $r->fr, 'ic' => $r->ic, 'pp' => $r->pp, 'sph' => $r->sph, 'spp' => $r->spp],
            'duration_ms' => $r->request_duration_ms,
            'created_at' => $r->created_at?->toIso8601String(),
            'result' => $r->plumber_response ?: null,
        ]);

        return response()->json($items);
    }

    public function compute(PlaygroundComputeRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $this->recommendationService->compute($this->droidRequest->build(data: $data, userId: $request->user()->id));

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(
                data: ['message' => $e->getMessage()],
                status: $e instanceof RecommendationException ? $e->getCode() : 422,
            );
        }
    }
}
