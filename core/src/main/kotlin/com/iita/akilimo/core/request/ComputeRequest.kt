package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder

import javax.validation.constraints.NotEmpty

@JsonIgnoreProperties(value = ["createdAt", "updatedAt"], allowGetters = true)
open class ComputeRequest {
    companion object {
        const val DEFAULT_EMPTY_VALUE = ""
        const val DEFAULT_UNAVAILABLE = "NA"
        const val DEFAULT_COST_PER_BAG = "NA"
        const val DEFAULT_BAG_WEIGHT_STRING = "NA"
        const val DEFAULT_BAG_WEIGHT_INT = 50
    }

    @NotEmpty(message = "Country is required")
    @JsonProperty("country")
    var country: String = DEFAULT_EMPTY_VALUE

    @JsonProperty("lat")
    var mapLat: Double? = null

    @JsonProperty("lon")
    var mapLong: Double? = null

    @JsonProperty("area")
    var fieldArea: Double? = 1.0

    @JsonProperty("areaUnits")
    var areaUnits: String? = null

    @JsonProperty("intercrop")
    var intercrop: Boolean = false

    @JsonProperty("IC")
    var interCroppingRec: Boolean = false

    @JsonProperty("IC_MAIZE")
    var interCroppingMaizeRec: Boolean = false

    @JsonProperty("IC_SP")
    var interCroppingPotatoRec: Boolean = false

    @JsonProperty("FR")
    var fertilizerRec: Boolean = false

    @JsonProperty("PP")
    var plantingPracticesRec: Boolean = false

    @JsonProperty("SPP")
    var scheduledPlantingRec: Boolean = false

    @JsonProperty("SPH")
    var scheduledHarvestRec: Boolean = false

    @JsonProperty("PD")
    var plantingDate: String? = null

    @JsonProperty("HD")
    var harvestDate: String? = null

    @JsonProperty("PD_window")
    var plantingDateWindow: Int = 0

    @JsonProperty("HD_window")

    var harvestDateWindow: Int = 0

    @JsonProperty("fallowType")
    var fallowType: String? = null

    @JsonProperty("fallowHeight")
    var fallowHeight: Int = 0

    @JsonProperty("fallowGreen")
    var fallowGreen: Boolean = false

    @JsonProperty("problemWeeds")
    var problemWeeds: Boolean = false

    @JsonProperty("tractor_plough")
    var tractorPlough: Boolean = false

    @JsonProperty("tractor_harrow")
    var tractorHarrow: Boolean = false

    @JsonProperty("tractor_ridger")
    var tractorRidger: Boolean = false

    @JsonProperty("cost_LMO_areaBasis")
    var costLmoAreaBasis: String? = null

    @JsonProperty("cost_tractor_ploughing")
    var costTractorPloughing: String? = null

    @JsonProperty("cost_tractor_harrowing")
    var costTractorHarrowing: String? = null

    @JsonProperty("cost_tractor_ridging")
    var costTractorRidging: String? = null

    @JsonProperty("cost_manual_ploughing")
    var costManualPloughing: String? = null

    @JsonProperty("cost_manual_harrowing")
    var costManualHarrowing: String? = null

    @JsonProperty("cost_manual_ridging")
    var costManualRidging: String? = null

    @JsonProperty("cost_weeding1")
    var costWeeding1: String? = null

    @JsonProperty("cost_weeding2")
    var costWeeding2: String? = null

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

    @JsonProperty("FCY")
    var currentFieldYield: Int? = null

    @JsonProperty("CMP")
    var currentMaizePerformance: String? = null

    @JsonProperty("saleSF")
    var sellToStarchFactory: Boolean = false

    @JsonProperty("nameSF")
    var starchFactoryName: String? = null

    @JsonProperty("cassPD")
    var cassavaProduceType: String? = null

    @JsonProperty("cassUW")
    var cassavaUnitWeight: Int = 1

    @JsonProperty("cassUP")
    var cassavaUnitPrice: String? = null

    @JsonProperty("cassUP_m1")
    var cassUPM1: String? = null

    @JsonProperty("cassUP_m2")
    var cassUpM2: String? = null

    @JsonProperty("cassUP_p1")
    var cassUpP1: String? = null

    @JsonProperty("cassUP_p2")
    var cassUpP2: String? = null

    @JsonProperty("maizePD")
    var maizeProduceType: String? = null

    @JsonProperty("maizeUW")
    var maizeUnitWeight: String? = null

    @JsonProperty("maizeUP")
    var maizeUnitPrice: String? = null

    @JsonProperty("sweetPotatoPD")
    var sweetPotatoProduceType: String? = null

    @JsonProperty("sweetPotatoUW")
    var sweetPotatoUnitWeight: String? = null

    @JsonProperty("sweetPotatoUP")
    var sweetPotatoUnitPrice: String? = null


    @JsonProperty("maxInv")
    var maxInvestment: String? = null

    @JsonProperty("riskAtt")
    var riskAttitude: Int = 0
}
