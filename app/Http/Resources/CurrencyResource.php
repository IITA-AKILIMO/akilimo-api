<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** Unique identifier for the currency */
            'id' => $this->id,
            /** Country name */
            'country' => $this->country ?? 'NA',
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $this->country_code ?? 'NA',
            /** Full name of the currency */
            'currency_name' => $this->currency_name,
            /** ISO 4217 currency code */
            'currency_code' => $this->currency_code,
            /** Currency symbol */
            'currency_symbol' => $this->currency_symbol,
            /** Native currency symbol */
            'currency_native_symbol' => $this->currency_native_symbol,
            /** Plural form of currency name */
            'currency_name_plural' => $this->name_plural,
        ];
    }
}
