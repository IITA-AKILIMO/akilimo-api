package com.iita.akilimo.core.request.usecases.sp

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumCountry


class BppComputeRequest(
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

    @JsonProperty("tractor_plough")
    var tractorPlough: Boolean = false

    @JsonProperty("tractor_harrow")
    var tractorHarrow: Boolean = false

    @JsonProperty("tractor_ridger")
    var tractorRidger: Boolean = false

    @JsonProperty("cost_LMO_areaBasis")
    var costLmoAreaBasis: String? = null

    @JsonProperty("cost_tractor_ploughing")
    var costTractorPloughing: Double? = null

    @JsonProperty("cost_tractor_harrowing")
    var costTractorHarrowing: Double? = null

    @JsonProperty("cost_tractor_ridging")
    var costTractorRidging: Double? = null

    @JsonProperty("cost_manual_ploughing")
    var costManualPloughing: Double? = null

    @JsonProperty("cost_manual_harrowing")
    var costManualHarrowing: Double? = null

    @JsonProperty("cost_manual_ridging")
    var costManualRidging: Double? = null

    @JsonProperty("cost_weeding1")
    var costWeeding1: Double? = null

    @JsonProperty("cost_weeding2")
    var costWeeding2: Double? = null

    @JsonProperty("ploughing")
    var ploughing: Boolean = false

    @JsonProperty("harrowing")
    var harrowing: Boolean = false

    @JsonProperty("ridging")
    var ridging: Boolean = false

    @JsonProperty("method_ploughing")
    var methodPloughing: String? = null

    @JsonProperty("method_harrowing")
    var methodHarrowing: String? = null

    @JsonProperty("method_ridging")
    var methodRidging: String? = null

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
