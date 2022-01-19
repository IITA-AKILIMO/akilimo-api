package com.iita.akilimo.core.request.usecases.ic

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumCountry


class IcComputeRequest(
    @JsonProperty("country") val country: EnumCountry,
    @JsonProperty("CMP") var currentMaizePerformance: Int,
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

//    @JsonProperty("IC_MAIZE")
//    var interCroppingMaizeRec: Boolean = false
//
//    @JsonProperty("IC_SP")
//    var interCroppingPotatoRec: Boolean = false

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

    @JsonProperty("maizePD")
    var maizeProduceType: String? = null

    @JsonProperty("maizeUW")
    var maizeUnitWeight: Double? = null

    @JsonProperty("maizeUP")
    var maizeUnitPrice: Double? = null

    @JsonProperty("sweetPotatoPD")
    var sweetPotatoProduceType: String? = null

    @JsonProperty("sweetPotatoUW")
    var sweetPotatoUnitWeight: Double? = null

    @JsonProperty("sweetPotatoUP")
    var sweetPotatoUnitPrice: Double? = null

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
