package com.iita.akilimo.core.mapper


import com.fasterxml.jackson.annotation.JsonProperty
import java.math.BigDecimal
import java.time.LocalDateTime


class FertilizerPriceDto {

    var id: Long? = null

    var priceId: Long? = null

    var sortOrder: Long? = null

    var fertilizerKey: String? = null

    var minLocalPrice: BigDecimal? = null

    var maxLocalPrice: BigDecimal? = null

    var minAllowedPrice: BigDecimal? = null

    var maxAllowedPrice: BigDecimal? = null

    var pricePerBag: BigDecimal? = null

    var active: Boolean? = null

    @JsonProperty("description")
    var desc: String? = null

    var priceRange: String? = null

    var country: String? = null

    var fertilizerCountry: String? = null
}
