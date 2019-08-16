package com.acai.akilimo.utils

import org.slf4j.LoggerFactory
import kotlin.math.roundToInt

class CurrencyConversion {

    private val logger = LoggerFactory.getLogger(CurrencyConversion::class.java)


    fun convertFertilizerPriceToLocalCurrency(minUsd: Double, maxUsd: Double, currencyRate: Double, nearestValue: Double, toCurrency: String?): String {
        val rangeString: String
        val minAmount: String
        val maxAmount: String

        when (toCurrency) {
            "USD" -> {
                return "$minUsd TO $maxUsd $toCurrency"
            }
        }

        val min = roundToNearestSpecifiedValue(numberToRound = convertToSpecifiedCurrency(minUsd, currencyRate), roundToNearest = nearestValue)
        minAmount = formatNumber(number = min)

        val max = roundToNearestSpecifiedValue(numberToRound = convertToSpecifiedCurrency(maxUsd, currencyRate), roundToNearest = nearestValue)
        maxAmount = formatNumber(number = max, toCurrency = toCurrency)


        rangeString = "$minAmount TO $maxAmount"

        return rangeString
    }

    fun convertToSpecifiedCurrency(fromAmount: Double, exchangeRate: Double): Double {
        return fromAmount * exchangeRate
    }

    fun convertFromSpecifiedCurrency(fromAmount: Double, exchangeRate: Double): Double {
        return fromAmount / exchangeRate
    }

    fun roundToNearestSpecifiedValue(numberToRound: Double, roundToNearest: Double): Double {
        val rounded = (numberToRound / roundToNearest).roundToInt() * roundToNearest
        return if (rounded > 0) rounded else numberToRound
    }


    private fun formatNumber(number: Double, toCurrency: String? = null): String {
        return if (toCurrency == null) {
            String.format("%,.0f", number)
        } else String.format("%,.0f $toCurrency", number)
    }
}