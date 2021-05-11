package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumCountry


open class ProducePriceRequest(
    @JsonProperty("country")
    var country: EnumCountry,
    var minLocalPrice: Double,
    var maxLocalPrice: Double
) {
    var sortOrder: Int = 1
    var minUsd: Double = 0.0
    var maxUsd: Double = 0.0
    var active: Boolean = true
}
