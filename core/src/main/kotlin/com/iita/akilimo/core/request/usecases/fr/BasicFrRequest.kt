package com.iita.akilimo.core.request.usecases.fr

import com.fasterxml.jackson.annotation.JsonProperty
import javax.validation.constraints.Pattern


data class BasicFrRequest(
    @JsonProperty("country") val country: String,
    @JsonProperty("FCY") val currentFieldYield: Int,
    @JsonProperty("lat") val lat: Double,
    @JsonProperty("lon") val lon: Double,
    @JsonProperty("area") val area: Double,
    @JsonProperty("maxInv") val maxInv: Double,
    @JsonProperty("cassUP") val cassUp: Double,
    @JsonProperty("areaUnit") val areaUnit: String,
    @JsonProperty("plantingMonth") @Pattern(regexp = "[1-9]+") val plantingMonth: Int,
    val ureaAvailable: Boolean,
    val ureaPrice: Double,
    val npk15Available: Boolean,
    val npk15Price: Double,
    val npk17Available: Boolean,
    val npk17Price: Double,
)
