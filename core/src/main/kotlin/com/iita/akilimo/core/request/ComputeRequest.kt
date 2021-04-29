package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import io.swagger.annotations.ApiModelProperty

import javax.validation.constraints.NotEmpty


@JsonPropertyOrder(
        "CMP",
        "FCY",
        "FR",
        "IC",
        "PP",
        "SPH",
        "SPP",
        "PD",
        "PD_window",
        "HD",
        "HD_window",
        "area",
        "areaUnits",
        "cassPD",
        "cassUP",
        "cassUP_m1",
        "cassUP_m2",
        "cassUP_p1",
        "cassUP_p2",
        "cassUW",
        "cost_LMO_areaBasis",
        "cost_manual_harrowing",
        "cost_manual_ploughing",
        "cost_manual_ridging",
        "cost_tractor_harrowing",
        "cost_tractor_ploughing",
        "cost_tractor_ridging",
        "cost_weeding1",
        "cost_weeding2",
        "country",
        "email",
        "SMS",
        "fallowGreen",
        "fallowHeight",
        "fallowType",
        "harrowing",
        "intercrop",
        "lat",
        "lon",
        "maizePD",
        "maizeUP",
        "maizeUW",
        "sweetPotatoPD",
        "sweetPotatoUW",
        "sweetPotatoUP",
        "maxInv",
        "method_harrowing",
        "method_ploughing",
        "method_ridging",
        "nameSF",
        "ploughing",
        "problemWeeds",
        "ridging",
        "riskAtt",
        "saleSF",
        "tractor_harrow",
        "tractor_plough",
        "tractor_ridger",
        "userEmail",
        "userField",
        "userName",
        "userPhoneCC",
        "userPhoneNr")
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
    @ApiModelProperty(example = "NG", required = true)
    var country: String = DEFAULT_EMPTY_VALUE

    @JsonProperty("lat")
    @ApiModelProperty(example = "8.725", required = true)
    var mapLat: Double? = null

    @JsonProperty("lon")
    @ApiModelProperty(example = "4.025", required = true)
    var mapLong: Double? = null

    @JsonProperty("area")
    @ApiModelProperty(example = "1", required = true)
    var fieldArea: Double? = 1.0

    @JsonProperty("areaUnits")
    @ApiModelProperty(example = "ha", required = true)
    var areaUnits: String? = null

    @JsonProperty("intercrop")
    @ApiModelProperty(required = true)
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
    @ApiModelProperty(example = "2018-09-11", required = true)
    var plantingDate: String? = null

    @JsonProperty("HD")
    @ApiModelProperty(example = "2019-08-27", required = true)
    var harvestDate: String? = null

    @JsonProperty("PD_window")
    @ApiModelProperty(example = "1", required = true)
    var plantingDateWindow: Int = 0

    @JsonProperty("HD_window")
    @ApiModelProperty(example = "1", required = true)
    var harvestDateWindow: Int = 0

    @JsonProperty("fallowType")
    @ApiModelProperty(example = "bush", required = true)
    var fallowType: String? = null

    @JsonProperty("fallowHeight")
    @ApiModelProperty(example = "100", required = true)
    var fallowHeight: Int = 0

    @JsonProperty("fallowGreen")
    var fallowGreen: Boolean = false

    @JsonProperty("problemWeeds")
    var problemWeeds: Boolean = false

    @JsonProperty("tractor_plough")
    @ApiModelProperty(required = true)
    var tractorPlough: Boolean = false

    @JsonProperty("tractor_harrow")
    @ApiModelProperty(example = "areaUnit", required = true, value = "false")
    var tractorHarrow: Boolean = false

    @JsonProperty("tractor_ridger")
    @ApiModelProperty(required = true)
    var tractorRidger: Boolean = false

    @JsonProperty("cost_LMO_areaBasis")
    @ApiModelProperty(example = "areaUnit", required = true)
    var costLMOAreaBasis: String? = null

    @JsonProperty("cost_tractor_ploughing")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var costTractorPloughing: String? = null

    @JsonProperty("cost_tractor_harrowing")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var costTractorHarrowing: String? = null

    @JsonProperty("cost_tractor_ridging")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var costTractorRidging: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("cost_manual_ploughing")
    var costManualPloughing: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("cost_manual_harrowing")
    var costManualHarrowing: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("cost_manual_ridging")
    var costManualRidging: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("cost_weeding1")
    var costWeeding1: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("cost_weeding2")
    var costWeeding2: String? = null

    @ApiModelProperty(required = true)
    @JsonProperty("ploughing")
    var ploughing: Boolean = false

    @ApiModelProperty(required = true)
    @JsonProperty("harrowing")
    var harrowing: Boolean = false

    @ApiModelProperty(required = true)
    @JsonProperty("ridging")
    var ridging: Boolean = false

    @JsonProperty("method_ploughing")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var methodPloughing: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("method_harrowing")
    var methodHarrowing: String? = null

    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    @JsonProperty("method_ridging")
    var methodRidging: String? = null

    @JsonProperty("FCY")
    @ApiModelProperty(example = "11", required = true)
    var currentFieldYield: Int? = null

    @JsonProperty("CMP")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var currentMaizePerformance: String? = null

    @JsonProperty("saleSF")
    @ApiModelProperty(required = true)
    var sellToStarchFactory: Boolean = false

    @JsonProperty("nameSF")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var starchFactoryName: String? = null

    @JsonProperty("cassPD")
    @ApiModelProperty(example = "roots", required = true)
    var cassavaProduceType: String? = null

    @JsonProperty("cassUW")
    @ApiModelProperty(example = "1", required = true)
    var cassavaUnitWeight: Int = 1

    @JsonProperty("cassUP")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var cassavaUnitPrice: String? = null

    @JsonProperty("cassUP_m1")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var cassUPM1: String? = null

    @JsonProperty("cassUP_m2")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var cassUPM2: String? = null

    @JsonProperty("cassUP_p1")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var cassUPP1: String? = null

    @JsonProperty("cassUP_p2")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var cassUPP2: String? = null

    @JsonProperty("maizePD")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var maizeProduceType: String? = null

    @JsonProperty("maizeUW")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var maizeUnitWeight: String? = null

    @JsonProperty("maizeUP")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var maizeUnitPrice: String? = null

    @JsonProperty("sweetPotatoPD")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var sweetPotatoProduceType: String? = null

    @JsonProperty("sweetPotatoUW")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var sweetPotatoUnitWeight: String? = null

    @JsonProperty("sweetPotatoUP")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var sweetPotatoUnitPrice: String? = null


    @JsonProperty("maxInv")
    @ApiModelProperty(example = DEFAULT_UNAVAILABLE, required = true)
    var maxInvestment: String? = null

    @JsonProperty("riskAtt")
    var riskAttitude: Int = 0
}
