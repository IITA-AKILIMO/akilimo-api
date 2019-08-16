package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import lombok.Data
import javax.validation.constraints.Min
import javax.validation.constraints.NotNull
import javax.validation.constraints.Size

@JsonPropertyOrder("minUsd", "maxUsd", "active")
@Data
open class FertilizerPriceRequest {

    @NotNull
    val minUsd: Double? = null

    @NotNull
    val maxUsd: Double? = null

    @NotNull
    @Min(value = 10, message = "Bag price should be greater than zero")
    val pricePerBag: Double = 0.0

    @NotNull
    var active: Boolean = false
}