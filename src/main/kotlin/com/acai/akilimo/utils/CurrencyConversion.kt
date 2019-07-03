package com.acai.akilimo.utils

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.properties.CurrencyProperties
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Component
import org.springframework.stereotype.Service
import kotlin.math.roundToInt

class CurrencyConversion {

    private val logger = LoggerFactory.getLogger(CurrencyConversion::class.java)


    fun convertFertilizerPriceToLocalCurrency(minUsd: Double, maxUsd: Double, currencyRate: Double, toCurrency: String?): String {
        val rangeString: String
        val minAmount: String
        val maxAmount: String
        val nearestValue = 100.00

        when (toCurrency) {
            "USD" -> {
                return "$minUsd TO $maxUsd $toCurrency"
            }
        }

        val min = roundToNearestSpecifiedValue((minUsd * currencyRate), nearestValue)
        minAmount = formatNumber(min, null)

        val max = roundToNearestSpecifiedValue((maxUsd * currencyRate), nearestValue)
        maxAmount = formatNumber(max, toCurrency)


        rangeString = "$minAmount TO $maxAmount"

        return rangeString
    }


    private fun roundToNearestSpecifiedValue(numberToRound: Double, roundToNearest: Double): Double {
        val rounded = (numberToRound / roundToNearest).roundToInt() * roundToNearest
        return if (rounded > 0) rounded else numberToRound
    }

    private fun formatNumber(number: Double, toCurrency: String?): String {
        return if (toCurrency == null) {
            String.format("%,.0f", number)
        } else String.format("%,.0f $toCurrency", number)
    }
}