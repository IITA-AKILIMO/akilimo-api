package com.acai.akilimo.mapper

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.utils.CurrencyConversion
import com.fasterxml.jackson.annotation.JsonIgnore
import lombok.Data

@Data
class FertilizerPriceDto {

    var priceId: Long? = null

    var minUsd: Double? = null

    var maxUsd: Double? = null

    var pricePerBag: Double? = null

    var active: Boolean? = null

    var description: String? = null

    var priceRange: String? = null
    var country: String? = null
}