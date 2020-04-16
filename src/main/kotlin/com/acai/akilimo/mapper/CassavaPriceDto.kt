package com.acai.akilimo.mapper

import com.acai.akilimo.enums.EnumCountry
import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data
import java.time.LocalDateTime

@Data
class CassavaPriceDto {
    var priceId: Long? = null

    var country: EnumCountry? = null

    var minLocalPrice: Double = 0.0

    var maxLocalPrice: Double = 0.0

    var minUsd: Double = 0.0

    var maxUsd: Double = 0.0

    var active: Boolean = false

    var createdAt: LocalDateTime? = null

    var updatedAt: LocalDateTime? = null

    @JsonProperty("averagePrice")
    fun averagePrice(): Double {
        return (minLocalPrice + maxLocalPrice) / 2
    }
}