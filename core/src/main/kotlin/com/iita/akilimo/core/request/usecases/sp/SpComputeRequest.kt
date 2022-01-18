package com.iita.akilimo.core.request.usecases.sp

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import com.iita.akilimo.core.request.ComputeRequest
import com.iita.akilimo.enums.EnumCountry
import javax.validation.constraints.Min

import javax.validation.constraints.NotEmpty


class SpComputeRequest(
    @JsonProperty("country") val country: EnumCountry,
    @JsonProperty("FCY") val currentFieldYield: Int,
    @JsonProperty("lat") val mapLat: Double,
    @JsonProperty("lon") val mapLong: Double,
    @JsonProperty("area") val fieldArea: Double,
    @JsonProperty("areaUnits") val areaUnits: String,
    @JsonProperty("PD") val plantingDate: String,
    @JsonProperty("HD") val harvestDate: String,
    @JsonProperty("PD_window") val plantingDateWindow: Int,
    @JsonProperty("HD_window") val harvestDateWindow: Int
) {

    @JsonProperty("saleSF")
    var sellToStarchFactory: Boolean? = null

    @JsonProperty("nameSF")
    var starchFactoryName: String? = null

    @JsonProperty("cassPD")
    var cassavaProduceType: String? = null

    @JsonProperty("cassUW")
    var cassavaUnitWeight: Int? = null

    @JsonProperty("cassUP")
    var cassavaUnitPrice: Double? = null

    @JsonProperty("cassUP_m1")
    var cassUpM1: Double? = null

    @JsonProperty("cassUP_m2")
    var cassUpM2: Double? = null

    @JsonProperty("cassUP_p1")
    var cassUpP1: Double? = null

    @JsonProperty("cassUP_p2")
    var cassUpP2: Double? = null

    @JsonProperty("maxInv")
    var maxInvestment: Double? = null

    @JsonProperty("riskAtt")
    var riskAttitude: Int? = null
}
