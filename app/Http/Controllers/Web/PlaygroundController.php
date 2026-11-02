<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\RecommendationException;
use App\Models\ApiRequest;
use App\Repositories\ApiRequestRepo;
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
    ) {}

    public function show(): View
    {
        return view('playground');
    }

    public function showHealth(): View
    {
        return view('playground-health');
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

    public function compute(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_code' => ['required', 'string', 'size:2'],
            'use_case' => ['required', 'string', 'in:FR,IC,PP,SPHS,COMPLETE'],
            'field_size' => ['required', 'numeric', 'min:0.1'],
            'area_unit' => ['required', 'string', 'in:acre,ha,m2,are'],
            'map_lat' => ['required', 'numeric', 'between:-90,90'],
            'map_long' => ['required', 'numeric', 'between:-180,180'],
            'lang' => ['sometimes', 'string', 'size:2'],
            // Crop & yield
            'field_yield' => ['required', 'integer', 'min:0', 'max:100'],
            'soil_quality' => ['required', 'numeric', 'min:0', 'max:5'],
            'risk_attitude' => ['required', 'integer', 'in:0,1,2'],
            'cassava_produce_type' => ['required', 'string', 'in:roots,chips,flour,gari'],
            'maize_produce_type' => ['sometimes', 'string', 'in:fresh_cob,grain'],
            'sweet_potato_produce_type' => ['sometimes', 'string', 'in:tubers,flour'],
            // Selling prices, in local currency per unit. With a price of 0 extra yield
            // is worth nothing, so fertilizer and intercropping could never pay.
            'cassava_unit_price' => ['required', 'numeric', 'gt:0'],
            'cassava_unit_weight' => ['required', 'numeric', 'gt:0'],
            'maize_unit_price' => ['required_if_accepted:inter_cropping_maize_rec', 'nullable', 'numeric', 'min:0'],
            'sweet_potato_unit_price' => ['required_if_accepted:inter_cropping_potato_rec', 'nullable', 'numeric', 'min:0'],
            // Recommendation flags
            'fertilizer_rec' => ['required', 'boolean'],
            'planting_practices_rec' => ['required', 'boolean'],
            'scheduled_planting_rec' => ['required', 'boolean'],
            'scheduled_harvest_rec' => ['required', 'boolean'],
            'inter_cropping_maize_rec' => ['sometimes', 'boolean'],
            'inter_cropping_potato_rec' => ['sometimes', 'boolean'],
            // Dates
            'planting_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now()->subMonths(6)->toDateString()],
            'harvest_date' => ['required', 'date_format:Y-m-d', 'after:planting_date',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $planting = $request->input('planting_date');
                    if (! $planting) {
                        return;
                    }
                    $plantingTs = strtotime($planting);
                    $harvestTs = strtotime($value);
                    // ~7.8 months = 7 months + 24 days
                    if ($harvestTs < strtotime('+7 months +24 days', $plantingTs)) {
                        $fail('Harvest date must be at least ~7.8 months after the planting date.');
                    }
                    if ($harvestTs > strtotime('+15 months', $plantingTs)) {
                        $fail('Harvest date must be within 15 months of the planting date.');
                    }
                },
            ],
            'planting_date_window' => ['required', 'integer', 'in:0,1,2'],
            'harvest_date_window' => ['required', 'integer', 'in:0,1,2'],
            // Fertilizers
            'fertilizer_list' => ['required', 'array', 'min:1'],
            'fertilizer_list.*.key' => ['required', 'string'],
            'fertilizer_list.*.name' => ['required', 'string'],
            'fertilizer_list.*.fertilizer_type' => ['required', 'string'],
            'fertilizer_list.*.weight' => ['required', 'numeric', 'min:0'],
            'fertilizer_list.*.price' => ['required', 'numeric', 'min:0'],
            'fertilizer_list.*.selected' => ['required', 'boolean'],
        ]);

        try {
            $result = $this->recommendationService->compute($this->buildDroidRequest($data, $request));

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e instanceof RecommendationException ? $e->getCode() : 422,
            );
        }
    }

    private function buildDroidRequest(array $data, Request $request): array
    {
        $countryCode = strtoupper($data['country_code']);
        $lang = $data['lang'] ?? 'en';
        $cassavaProduce = $data['cassava_produce_type'] ?? 'roots';
        $maizeProduce = $data['maize_produce_type'] ?? 'fresh_cob';
        $potatoProduce = $data['sweet_potato_produce_type'] ?? 'tubers';

        return [
            'user_info' => [
                'device_token' => 'playground-'.$request->user()->id,
                'farm_name' => 'Demo Farm',
                'first_name' => 'Demo',
                'last_name' => 'User',
                'user_name' => 'demo_user',
                'email_address' => null,
                'phone_number' => null,
                'gender' => 'M',
                'send_email' => false,
                'send_sms' => false,
                'risk_attitude' => (int) $data['risk_attitude'],
            ],
            'compute_request' => [
                'farmInformation' => [
                    'country_code' => $countryCode,
                    'use_case' => strtoupper($data['use_case']),
                    'map_lat' => (float) $data['map_lat'],
                    'map_long' => (float) $data['map_long'],
                    'field_size' => (float) $data['field_size'],
                    'area_unit' => $data['area_unit'],
                ],
                'interCropping' => [
                    'inter_cropped_crop' => null,
                    'inter_cropping_maize_rec' => (bool) ($data['inter_cropping_maize_rec'] ?? false),
                    'inter_cropping_potato_rec' => (bool) ($data['inter_cropping_potato_rec'] ?? false),
                ],
                'recommendations' => [
                    'lang' => $lang,
                    'fertilizer_rec' => (bool) $data['fertilizer_rec'],
                    'planting_practices_rec' => (bool) $data['planting_practices_rec'],
                    'scheduled_planting_rec' => (bool) $data['scheduled_planting_rec'],
                    'scheduled_harvest_rec' => (bool) $data['scheduled_harvest_rec'],
                ],
                'planting' => [
                    'planting_date' => $data['planting_date'],
                    'planting_date_window' => (int) $data['planting_date_window'],
                    'harvest_date' => $data['harvest_date'],
                    'harvest_date_window' => (int) $data['harvest_date_window'],
                ],
                'fallow' => [
                    'fallow_type' => 'none',
                    'fallow_height' => 100,
                    'fallow_green' => false,
                ],
                'tractorCosts' => [
                    'tractor_plough' => false,
                    'tractor_harrow' => false,
                    'tractor_ridger' => false,
                    'cost_lmo_area_basis' => $data['area_unit'],
                    'cost_tractor_ploughing' => 0.0,
                    'cost_tractor_harrowing' => 0.0,
                    'cost_tractor_ridging' => 0.0,
                ],
                'manualCosts' => [
                    'cost_manual_ploughing' => 0.0,
                    'cost_manual_harrowing' => 0.0,
                    'cost_manual_ridging' => 0.0,
                ],
                'weedingCosts' => [
                    'cost_weeding_one' => 0.0,
                    'cost_weeding_two' => 0.0,
                ],
                'operationsDone' => [
                    'ploughing_done' => false,
                    'harrowing_done' => false,
                    'ridging_done' => false,
                ],
                'methods' => [
                    'method_ploughing' => 'manual',
                    'method_harrowing' => 'manual',
                    'method_ridging' => 'manual',
                    'method_weeding' => 'manual',
                ],
                'yieldInfo' => [
                    'current_field_yield' => (int) $data['field_yield'],
                    'current_maize_performance' => (float) $data['soil_quality'],
                    'sell_to_starch_factory' => false,
                    'starch_factory_name' => null,
                ],
                // The price is per selling unit (1, 50, 100 or 1000 kg, from cassava-units)
                'cassava' => [
                    'produce_type' => $cassavaProduce,
                    'unit_weight' => (float) $data['cassava_unit_weight'],
                    'unit_price' => (float) $data['cassava_unit_price'],
                ],
                // Maize is priced per cob, or per 50 kg bag of grain; sweet potato per tonne
                'maize' => [
                    'produce_type' => $maizeProduce,
                    'unit_weight' => $maizeProduce === 'grain' ? 50.0 : 1.0,
                    'unit_price' => (float) ($data['maize_unit_price'] ?? 0),
                ],
                'sweetPotato' => [
                    'produce_type' => $potatoProduce,
                    'unit_weight' => 1000.0,
                    'unit_price' => (float) ($data['sweet_potato_unit_price'] ?? 0),
                ],
                'maxInvestment' => 0.0,
            ],
            'fertilizer_list' => $data['fertilizer_list'],
        ];
    }
}
