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

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Currency $currency */
        $currency = $this->resource;

        return [
            /** Unique identifier for the currency */
            'id' => $currency->id,
            /** Country name */
            'country' => $currency->country ?? 'NA',
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $currency->country_code ?? 'NA',
            /** Full name of the currency */
            'currency_name' => $currency->currency_name,
            /** ISO 4217 currency code */
            'currency_code' => $currency->currency_code,
            /** Currency symbol */
            'currency_symbol' => $currency->currency_symbol,
            /** Native currency symbol */
            'currency_native_symbol' => $currency->currency_native_symbol,
            /** Plural form of currency name */
            'currency_name_plural' => $currency->name_plural,
            //            'created_at' => $currency->created_at,
            //            'updated_at' => $currency->updated_at,
        ];
    }
}
