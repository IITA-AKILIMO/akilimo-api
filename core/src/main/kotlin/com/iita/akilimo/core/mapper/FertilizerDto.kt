package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import java.math.BigDecimal


class FertilizerDto {

    var fertilizerId: Long? = null

    var name: String? = null

    var type: String? = null

    var weight: Int? = null

    var price: BigDecimal? = null

    var currency: String? = null

    var countryCode: String? = null

    var fertilizerCountry: String? = null

    var useCase: String? = null
}
