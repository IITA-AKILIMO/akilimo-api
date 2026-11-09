<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Utils;

use App\Models\Currency as CurrencyDto;
use Illuminate\Support\Str;

class CurrencyConversion
{
    public function convertPriceToLocalCurrency(
        float $minUsd,
        float $maxUsd,
        float $currencyRate,
        float $nearestValue,
        CurrencyDto $currency,
    ): string {
        $min = $this->roundToNearestSpecifiedValue(
            $this->convertToSpecifiedCurrencyValue($minUsd, $currencyRate),
            $nearestValue
        );
        $minAmount = $this->formatNumber($min);

        $max = $this->roundToNearestSpecifiedValue(
            $this->convertToSpecifiedCurrencyValue($maxUsd, $currencyRate),
            $nearestValue
        );

        return $this->formatNumber($max, $currency);
    }

    public function convertToSpecifiedCurrency(
        float $amount,
        float $currencyRate,
        float $nearestValue,
        CurrencyDto $currencyDto,
    ): float {
        if (Str::isMatch($currencyDto->currency_code, 'USD')) {
            return $amount;
        }

        return $this->roundToNearestSpecifiedValue(
            $this->convertToSpecifiedCurrencyValue($amount, $currencyRate),
            $nearestValue
        );
    }

    public function convertToSpecifiedCurrencyValue(float $fromAmount, float $exchangeRate): float
    {
        return $fromAmount * $exchangeRate;
    }

    public function convertFromSpecifiedCurrency(float $fromAmount, float $exchangeRate): float
    {
        return $fromAmount / $exchangeRate;
    }

    public function roundToNearestSpecifiedValue(float $numberToRound, float $roundToNearest): float
    {
        $rounded = round($numberToRound / $roundToNearest) * $roundToNearest;

        return ($rounded > 0) ? $rounded : $numberToRound;
    }

    private function formatNumber(float $number, ?CurrencyDto $currencyDto = null): string
    {
        if (is_null($currencyDto)) {
            return number_format($number, 0, '.', ',');
        }

        $currency = $currencyDto->currency_symbol ?: $currencyDto->currency_code;

        return number_format($number, 0, '.', ',') . ' ' . $currency;
    }
}
