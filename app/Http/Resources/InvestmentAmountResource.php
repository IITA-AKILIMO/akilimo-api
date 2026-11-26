<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Currency;
use App\Models\VwInvestmentAmount;
use App\Support\PriceMarker;
use Illuminate\Http\Request;

/**
 * @mixin VwInvestmentAmount
 */
class InvestmentAmountResource extends BaseJsonResource
{
    public function __construct(
        $resource,
        private readonly ?Currency $currency = null,
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tag = $this->id;
        if ($this->investment_amount === -1.0) {
            $tag = 'exact';
        }

        $currencyResource = $this->currency ? CurrencyResource::make($this->currency) : null;

        return [
            /** Unique identifier for the investment amount */
            'id' => $this->id,
            /** Investment amount value */
            'investment_amount' => $this->investment_amount,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $this->country,
            /** Currency details */
            'currency' => $currencyResource,
            /** Whether this is an exact amount */
            'exact_amount' => $this->investment_amount === 0.0,
            /** The text to show for this choice: the amount with the currency, or "Exact amount" for the row where the farmer types their own */
            'label' => $this->investment_amount === 0.0
                ? PriceMarker::EXACT_AMOUNT
                : PriceMarker::labelForPrice($this->investment_amount, $this->currency?->currency_symbol ?: $this->currency?->currency_code),
            /** Words for the row where the farmer types their own amount ("Exact amount"); empty for an offered amount */
            'description' => $this->investment_amount === 0.0 ? PriceMarker::EXACT_AMOUNT : '',
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Whether the record is active */
            'active' => $this->price_active,
            /** Area unit (e.g. ha, acre) */
            'area_unit' => $this->area_unit,
            /** Sort order for display */
            'sort_order' => $this->sort_order,
        ];
    }
}
