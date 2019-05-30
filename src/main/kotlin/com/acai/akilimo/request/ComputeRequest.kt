package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import com.fasterxml.jackson.annotation.JsonProperty
import io.swagger.annotations.ApiModelProperty

import lombok.Data

import java.time.LocalDate

@Data
@JsonIgnoreProperties(value = ["createdAt", "updatedAt"], allowGetters = true)
class ComputeRequest {
    companion object {
        const val DEFAULT_UNAVAILABLE = "NA"
    }

    @JsonProperty("country")
    var country: String? = null

    @JsonProperty("lat")
    var mapLat: Double? = null

    @JsonProperty("lon")
    var mapLong: Double? = null

    @JsonProperty("area")
    var fieldArea: Double? = 1.0

    @JsonProperty("areaUnits")
    var areaUnits: String? = null

    @JsonProperty("intercrop")
    var intercrop: String? = null

    @JsonProperty("IC")
    var interCroppingRec: Boolean = false

    @JsonProperty("FR")
    var fertilizerRec: Boolean = false

    @JsonProperty("PlantingPractices")
    var plantingPRacticesRec: Boolean = false

    @JsonProperty("SPP")
    var scheduledPlantingRec: Boolean = false

    @JsonProperty("SPH")
    var scheduledHarvestRec: Boolean = false

    @JsonProperty("PD")
    @ApiModelProperty(example = "2019-05-15", required = true)
    var plantingDate: LocalDate? = null

    @JsonProperty("HD")
    @ApiModelProperty(example = "2019-05-15", required = true)
    var harvestDate: LocalDate? = null

    @JsonProperty("PD_window")
    var plantingDateWindow = 0

    @JsonProperty("HD_window")
    var harvestDateWindow = 0

    @JsonProperty("fallowType")
    var fallowType: String? = null

    @JsonProperty("fallowHeight")
    var fallowHeight: Int = 0

    @JsonProperty("fallowGreen")
    var fallowGreen = false

    @JsonProperty("problemWeeds")
    var problemWeeds = false

    @JsonProperty("tractor_plough")
    var tractorPlough: String? = null

    @JsonProperty("tractor_harrow")
    var tractorHarrow: String? = null

    @JsonProperty("tractor_ridger")
    var tractorRidger: String? = null

    @JsonProperty("cost_LMO_areaBasis")
    var costLMOAreaBasis: String? = null

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
    var ploughing: String? = null
    @JsonProperty("harrowing")
    var harrowing: String? = null
    @JsonProperty("ridging")
    var ridging: String? = null
    @JsonProperty("method_ploughing")
    var methodPloughing: String? = null
    @JsonProperty("method_harrowing")
    var methodHarrowing: String? = null
    @JsonProperty("method_ridging")
    var methodRidging: String? = null

    @JsonProperty("FCY")
    var currentFieldYield: String? = null

    @JsonProperty("CMP")
    var currentMaizePerformance: String? = null

    @JsonProperty("saleSF")
    var sellToStarchFactory: String? = null

    @JsonProperty("nameSF")
    var starchFactoryName: String? = null

    @JsonProperty("cassPD")
    var cassavaProduceType: String? = null

    @JsonProperty("cassUW")
    var cassavaUnitWeight = 1

    @JsonProperty("cassUP")
    var cassavaUnitPrice: Double = 0.toDouble()

    @JsonProperty("cassUP_m1")
    var cassUPM1: String? = null
    @JsonProperty("cassUP_m2")
    var cassUPM2: String? = null
    @JsonProperty("cassUP_p1")
    var cassUPP1: String? = null
    @JsonProperty("cassUP_p2")
    var cassUPP2: String? = null

    @JsonProperty("maizePD")
    var maizeProduceType: String? = null

    @JsonProperty("maizeUW")
    var maizeUnitWeight: String? = null

    @JsonProperty("maizeUP")
    var maizeUnitPrice: String? = null

    @JsonProperty("maxInv")
    var maxInvestment: String? = null

    @JsonProperty("SMS")
    var sendSms: Boolean = false

    @JsonProperty("email")
    var sendEmail: Boolean = false

    @JsonProperty("userPhoneCC")
    var mobileCountryCode: String? = null

    @JsonProperty("userPhoneNr")
    var mobileNumber: String? = null

    @JsonProperty("userName")
    var userName: String? = null

    @JsonProperty("userEmail")
    var userEmail: String? = null

    @JsonProperty("userField")
    var fieldDescription: String? = null

    @JsonProperty("riskAtt")
    var riskAttitude = 0

    @JsonProperty("ureaavailable")
    var ureaAvailable: Boolean = false
    @JsonProperty("ureaCostperBag")
    var ureaCostperBag: String? = null
    @JsonProperty("ureaBagWeight")
    var ureaBagWeight: String? = null

    @JsonProperty("MOPavailable")
    var mopAvailable: Boolean = false
    @JsonProperty("MOPCostperBag")
    var mopCostperBag: String? = null
    @JsonProperty("MOPBagWeight")
    var mopBagWeight: String? = null

    @JsonProperty("DAPavailable")
    var dapAvailable: Boolean = false
    @JsonProperty("DAPCostperBag")
    var dapCostperBag: String? = null
    @JsonProperty("DAPBagWeight")
    var dapBagWeight: String? = null

    @JsonProperty("NPK201010available")
    var nPK201010available: Boolean = false
    @JsonProperty("NPK201010CostperBag")
    var nPK201010CostperBag: String? = null
    @JsonProperty("NPK201010BagWeight")
    var nPK201010BagWeight: String? = null

    @JsonProperty("NPK151515available")
    var nPK151515available: Boolean = false
    @JsonProperty("NPK151515CostperBag")
    var nPK151515CostperBag: String? = null
    @JsonProperty("NPK151515BagWeight")
    var nPK151515BagWeight: String? = null

    @JsonProperty("TSPavailable")
    var tspAvailable: Boolean = false
    @JsonProperty("TSPCostperBag")
    var tspCostperBag: String? = null
    @JsonProperty("TSPBagWeight")
    var tspBagWeight: String? = null

    @JsonProperty("NPK171717available")
    var npK171717available: Boolean = false
    @JsonProperty("NPK171717CostperBag")
    var npK171717CostperBag: String? = null
    @JsonProperty("NPK171717BagWeight")
    var npK171717BagWeight: String? = null

    @JsonProperty("Nafakaavailable")
    var nafakaAvailable: Boolean = false
    @JsonProperty("NafakaCostperBag")
    var nafakaCostperBag: String? = null
    @JsonProperty("NafakaBagWeight")
    var nafakaBagWeight: String? = null

    @JsonProperty("CANavailable")
    var canAvailable: Boolean = false
    @JsonProperty("CANCostperBag")
    var canCostperBag: String? = null
    @JsonProperty("CANBagWeight")
    var canBagWeight: String? = null

    @JsonProperty("SSPavailable")
    var sspAvailable: Boolean = false
    @JsonProperty("SSPCostperBag")
    var sspCostperBag: String? = null
    @JsonProperty("SSPBagWeight")
    var sspBagWeight: String? = null

    @JsonProperty("newFert1name")
    var newFert1name: String? = null
    @JsonProperty("newFert1N_cont")
    var newFert1NCont: String? = null
    @JsonProperty("newFert1P2O5")
    var newFert1P2O5: String? = null
    @JsonProperty("newFertK2O")
    var newFertK2O: String? = null
    @JsonProperty("newFertCostperBag")
    var newFertCostperBag: String? = null
    @JsonProperty("newFert1BagWeight")
    var newFert1BagWeight: String? = null
    @JsonProperty("newFert2name")
    var newFert2name: String? = null
    @JsonProperty("newFert2N_cont")
    var newFert2NCont: String? = null
    @JsonProperty("newFert2P2O5")
    var newFert2P2O5: String? = null
    @JsonProperty("newFert2K2O")
    var newFert2K2O: String? = null
    @JsonProperty("newFert2CostperBag")
    var newFert2CostperBag: String? = null
    @JsonProperty("newFert2BagWeight")
    var newFert2BagWeight: String? = null
    @JsonProperty("newFert3name")
    var newFert3name: String? = null
    @JsonProperty("newFert3N_cont")
    var newFert3NCont: String? = null
    @JsonProperty("newFert3P2O5")
    var newFert3P2O5: String? = null
    @JsonProperty("newFert3K2O")
    var newFert3K2O: String? = null
    @JsonProperty("newFert3CostperBag")
    var newFert3CostperBag: String? = null
    @JsonProperty("newFert3BagWeight")
    var newFert3BagWeight: String? = null
    @JsonProperty("newFert4name")
    var newFert4name: String? = null
    @JsonProperty("newFert4N_cont")
    var newFert4NCont: String? = null
    @JsonProperty("newFert4P2O5")
    var newFert4P2O5: String? = null
    @JsonProperty("newFert4K2O")
    var newFert4K2O: String? = null
    @JsonProperty("newFert4CostperBag")
    var newFert4CostperBag: String? = null
    @JsonProperty("newFert4BagWeight")
    var newFert4BagWeight: String? = null
    @JsonProperty("newFert5name")
    var newFert5name: String? = null
    @JsonProperty("newFert5N_cont")
    var newFert5NCont: String? = null
    @JsonProperty("newFert5P2O5")
    var newFert5P2O5: String? = null
    @JsonProperty("newFert5K2O")
    var newFert5K2O: String? = null
    @JsonProperty("newFert5CostperBag")
    var newFert5CostperBag: String? = null
    @JsonProperty("newFert5BagWeight")
    var newFert5BagWeight: String? = null
}