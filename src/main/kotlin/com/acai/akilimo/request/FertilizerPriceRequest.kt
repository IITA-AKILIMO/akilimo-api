package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import lombok.Data

@JsonPropertyOrder("minUsd", "maxUsd", "active")
@Data
open class FertilizerPriceRequest {
    val minUsd: Double? = null

    val maxUsd: Double? = null

    var active: Boolean = false
}