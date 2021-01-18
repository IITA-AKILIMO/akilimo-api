package com.acai.akilimo.utils

import com.acai.akilimo.mapper.CurrencyDto
import org.slf4j.LoggerFactory
import java.util.*
import kotlin.math.roundToInt

class CurrencyConversion {

    private val logger = LoggerFactory.getLogger(CurrencyConversion::class.java)


    fun convertPriceToLocalCurrency(minUsd: Double, maxUsd: Double, currencyRate: Double, nearestValue: Double, currencyDto: CurrencyDto): String {
        val rangeString: String
        val minAmount: String
        val maxAmount: String

        val min = roundToNearestSpecifiedValue(numberToRound = convertToSpecifiedCurrency(minUsd, currencyRate), roundToNearest = nearestValue)
        minAmount = formatNumber(number = min)

        val max = roundToNearestSpecifiedValue(numberToRound = convertToSpecifiedCurrency(maxUsd, currencyRate), roundToNearest = nearestValue)
        maxAmount = formatNumber(number = max, currencyDto = currencyDto)

        rangeString = "About $maxAmount"

        return rangeString
    }

    fun convertToSpecifiedCurrency(amount: Double, currencyRate: Double, nearestValue: Double, currencyDto: CurrencyDto): Double {
        when (currencyDto.currencyCode) {
            "USD" -> {
                return amount
            }
        }
        return roundToNearestSpecifiedValue(numberToRound = convertToSpecifiedCurrency(amount, currencyRate), roundToNearest = nearestValue)
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


    private fun formatNumber(number: Double, currencyDto: CurrencyDto? = null): String {
        return if (currencyDto == null) {
            String.format("%,.0f", number)
        } else {
            var currency = currencyDto.currencySymbol
            if (currency.isNullOrEmpty()) {
                currency = currencyDto.currencyCode
            }
            String.format("%,.0f $currency", number)
        }
    }
}