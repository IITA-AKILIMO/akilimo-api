package com.iita.akilimo.core.request.usecases.fr

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumCountry


data class FrComputeRequest(
    @JsonProperty("country") val country: EnumCountry,
    @JsonProperty("FCY") val currentFieldYield: Int,
    @JsonProperty("lat") val mapLat: Double,
    @JsonProperty("lon") val mapLong: Double,
    @JsonProperty("area") val fieldArea: Double,
    @JsonProperty("areaUnits") val areaUnits: String,
    @JsonProperty("PD") val plantingDate: String,
    @JsonProperty("HD") val harvestDate: String,
    @JsonProperty("cassUP") val cassavaUnitPrice: Double
) {

    @JsonProperty("PD_window")
    var plantingDateWindow: Int = 0

    @JsonProperty("HD_window")
    var harvestDateWindow: Int = 0

    @JsonProperty("saleSF")
    var sellToStarchFactory: Boolean = false

    @JsonProperty("nameSF")
    var starchFactoryName: String = "NA"

    @JsonProperty("cassPD")
    var cassavaProduceType: String = "roots"

    @JsonProperty("cassUW")
    var cassavaUnitWeight: Int = 1000


    @JsonProperty("cassUP_m1")
    var cassUpM1: Double = 0.0

    @JsonProperty("cassUP_m2")
    var cassUpM2: Double = 0.0

    @JsonProperty("cassUP_p1")
    var cassUpP1: Double = 0.0

    @JsonProperty("cassUP_p2")
    var cassUpP2: Double = 0.0

    @JsonProperty("maxInv")
    var maxInvestment: Double = 0.0

    @JsonProperty("riskAtt")
    var riskAttitude: Int = 0
}
