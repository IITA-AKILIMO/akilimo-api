package com.acai.akilimo.mapper

import com.acai.akilimo.enums.EnumCountry
import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data
import java.time.LocalDateTime

@Data
class ProducePriceDto {

    var priceId: Long? = null

    var priceIndex: Long = 1

    var country: EnumCountry? = null

    var produceType: String? = null

    var minLocalPrice: Double = 0.0

    var maxLocalPrice: Double = 0.0

    var minAllowedPrice: Double = 0.0

    var maxAllowedPrice: Double = 0.0

    var minUsd: Double = 0.0

    var maxUsd: Double = 0.0

    var sortOrder: Int = 1

    var active: Boolean = false

    var createdAt: LocalDateTime? = null

    var updatedAt: LocalDateTime? = null

    @JsonProperty("averagePrice")
    fun averagePrice(): Double {
        return (minLocalPrice + maxLocalPrice) / 2
    }

    @JsonProperty("countryPrice")
    fun countryPrice(): String {
        return "$country$priceId";
    }
}