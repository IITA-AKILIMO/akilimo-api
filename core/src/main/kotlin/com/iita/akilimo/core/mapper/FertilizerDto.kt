package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import java.math.BigDecimal


class FertilizerDto {

    @JsonProperty("fertilizerId")
    var id: Long? = null

    var fertilizerKey: String? = null

    var name: String? = null

    @JsonProperty("type")
    var fertilizerType: String? = null

    var weight: Int? = null

    var currency: String? = null

    var countryCode: String? = null

    var fertilizerCountry: String? = null

    var useCase: String? = null
}
