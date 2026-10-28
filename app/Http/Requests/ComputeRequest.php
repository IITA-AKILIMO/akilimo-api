<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests;

use App\Rules\ComputeFieldRules;
use App\Rules\FertilizerRules;
use App\Rules\UserInfoRules;
use App\Service\CoverageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ComputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public API — no authentication required by design.
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            UserInfoRules::rules(),
            ComputeFieldRules::rules(),
            FertilizerRules::rules()
        );
    }

    /**
     * Refuse a field outside the supported area here, with a clear message, instead of letting the
     * compute service answer with an empty or failed recommendation. A country whose area has not
     * been synced yet (coverage:sync) is never refused.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || !config('akilimo-compute.coverage.validate_requests')) {
                return;
            }

            $farm = $this->input('compute_request.farmInformation', []);
            $country = strtoupper((string)($farm['country_code'] ?? ''));

            $covered = app(CoverageService::class)->contains($country, (float)$farm['map_lat'], (float)$farm['map_long']);

            if ($covered === false) {
                $validator->errors()->add(
                    'compute_request.farmInformation.map_lat',
                    "This location is outside the area AKILIMO supports in {$country}. Check the coordinates against GET /v1/coverage/{$country}.",
                );
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        // No pre-processing needed; email_address and phone_number are optional
        // and stored as-is (null when absent) so analytics queries stay accurate.
    }

    public function messages(): array
    {
        return array_merge(
            UserInfoRules::messages(),
            ComputeFieldRules::messages(),
            FertilizerRules::messages()
        );
    }
}
