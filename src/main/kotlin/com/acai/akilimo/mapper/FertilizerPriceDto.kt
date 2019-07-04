package com.acai.akilimo.mapper

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.utils.CurrencyConversion
import lombok.Data

@Data
class FertilizerPriceDto {

    var id: Long? = null
    var minUsd: Double? = null
    var maxUsd: Double? = null

    var active: Boolean? = null

    var priceRange: String? = null
}