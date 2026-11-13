<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Support\PriceMarker;
use App\Models\Currency;
use App\Models\VwInvestmentAmount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentAmountResource extends JsonResource
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
        /** @var VwInvestmentAmount $model */
        $model = $this->resource;

        $tag = $model->id;
        if ($model->investment_amount === -1.0) {
            $tag = 'exact';
        }

        $currencyResource = $this->currency ? CurrencyResource::make($this->currency) : null;

        return [
            /** Unique identifier for the investment amount */
            'id' => $model->id,
            /** Investment amount value */
            'investment_amount' => $model->investment_amount,
            /** ISO 3166-1 alpha-2 country code */
            'country_code' => $model->country,
            /** Currency details */
            'currency' => $currencyResource,
            /** Whether this is an exact amount */
            'exact_amount' => $model->investment_amount === 0.0,
            /** The text to show for this choice: the amount with the currency, or "Exact amount" for the row where the farmer types their own */
            'label' => (float) $model->investment_amount === 0.0
                ? PriceMarker::EXACT_AMOUNT
                : PriceMarker::labelForPrice($model->investment_amount, $this->currency?->currency_symbol ?: $this->currency?->currency_code),
            /** Words for the row where the farmer types their own amount ("Exact amount"); empty for an offered amount */
            'description' => (float) $model->investment_amount === 0.0 ? PriceMarker::EXACT_AMOUNT : '',
            /** Item tag identifier */
            'item_tag' => $tag,
            /** Whether the record is active */
            'active' => $model->price_active,
            /** Area unit (e.g. ha, acre) */
            'area_unit' => $model->area_unit,
            /** Sort order for display */
            'sort_order' => $model->sort_order,
        ];
    }
}
