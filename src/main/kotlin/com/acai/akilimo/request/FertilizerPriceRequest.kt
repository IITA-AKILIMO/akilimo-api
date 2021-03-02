package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder

import javax.validation.constraints.Min
import javax.validation.constraints.NotNull

@JsonPropertyOrder("minUsd", "maxUsd", "active")

open class FertilizerPriceRequest {

    @NotNull
    var minUsd: Double? = null

    @NotNull
    var maxUsd: Double? = null

    @NotNull
    var currencyCode: String = "USD"

    @NotNull
    @Min(value = 10, message = "Bag price should be greater than zero")
    var pricePerBag: Double = 0.0

    @NotNull
    var active: Boolean = false
}