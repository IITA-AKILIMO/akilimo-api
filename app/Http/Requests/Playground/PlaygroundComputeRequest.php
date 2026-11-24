<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Playground;

use App\Rules\HarvestDateWindow;
use Illuminate\Foundation\Http\FormRequest;

/** What the playground form posts to /playground/compute */
class PlaygroundComputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'size:2'],
            'use_case' => ['required', 'string', 'in:FR,IC,PP,SPHS,PDC,COMPLETE'],
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
            'planting_date_cultivar_rec' => ['sometimes', 'boolean'],
            'inter_cropping_maize_rec' => ['sometimes', 'boolean'],
            'inter_cropping_potato_rec' => ['sometimes', 'boolean'],
            // Dates
            'planting_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:' . now()->subMonths(6)->toDateString()],
            'harvest_date' => ['required', 'date_format:Y-m-d', 'after:planting_date', new HarvestDateWindow('planting_date')],
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
        ];
    }
}
