package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty

import javax.validation.constraints.NotEmpty

open class PlumberComputeRequest {
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
    var fieldArea: Double = 1.0

    @JsonProperty("areaUnits")
    var areaUnits: String = "acre"

    @JsonProperty("intercrop")
    var intercrop: Boolean = false

    @JsonProperty("IC")
    var interCroppingRec: Boolean = false

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
    var costTractorPloughing: Double = 0.0

    @JsonProperty("cost_tractor_harrowing")
    var costTractorHarrowing: Double = 0.0

    @JsonProperty("cost_tractor_ridging")
    var costTractorRidging: Double = 0.0

    @JsonProperty("cost_manual_ploughing")
    var costManualPloughing: Double = 0.0

    @JsonProperty("cost_manual_harrowing")
    var costManualHarrowing: Double = 0.0

    @JsonProperty("cost_manual_ridging")
    var costManualRidging: Double = 0.0

    @JsonProperty("cost_weeding1")
    var costWeeding1: Double = 0.0

    @JsonProperty("cost_weeding2")
    var costWeeding2: Double = 0.0

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
    var cassavaUnitWeight: Int = 0

    @JsonProperty("cassUP")
    var cassavaUnitPrice: Double = 0.0

    @JsonProperty("cassUP_m1")
    var cassUPM1: Double = 0.0

    @JsonProperty("cassUP_m2")
    var cassUPM2: Double = 0.0

    @JsonProperty("cassUP_p1")
    var cassUPP1: Double = 0.0

    @JsonProperty("cassUP_p2")
    var cassUPP2: Double = 0.0

    @JsonProperty("maizePD")
    var maizeProduceType: String? = null

    @JsonProperty("maizeUW")
    var maizeUnitWeight: Int = 0

    @JsonProperty("maizeUP")
    var maizeUnitPrice: Double = 0.0

    @JsonProperty("sweetPotatoPD")
    var sweetPotatoProduceType: String? = null

    @JsonProperty("sweetPotatoUW")
    var sweetPotatoUnitWeight: Int = 0

    @JsonProperty("sweetPotatoUP")
    var sweetPotatoUnitPrice: Double = 0.0


    @JsonProperty("maxInv")
    var maxInvestment: Double = 0.0

    @JsonProperty("SMS")
    var sendSms: Boolean = false

    @JsonProperty("email")
    var email: Boolean = false

    @JsonProperty("userPhoneCC")
    var mobileCountryCode: String? = null

    @JsonProperty("userPhoneNr")
    var mobileNumber: String? = null

    @JsonProperty("fullPhoneNumber")
    var fullPhoneNumber: String? = null

    @JsonProperty("userName")
    var userName: String? = null

    @JsonProperty("userEmail")
    var userEmail: String? = null

    @JsonProperty("userField")
    var fieldDescription: String? = null

    @JsonProperty("riskAtt")
    var riskAttitude: Int = 1

    @JsonProperty("ureaavailable")
    var ureaAvailable: Boolean = false

    @JsonProperty("ureaCostperBag")
    var ureaCostPerBag: Double = 0.0

    @JsonProperty("ureaBagWt")
    var ureaBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("MOPavailable")
    var mopAvailable: Boolean = false

    @JsonProperty("MOPCostperBag")
    var mopCostPerBag: Double = 0.0

    @JsonProperty("MOPBagWt")
    var mopBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("DAPavailable")
    var dapAvailable: Boolean = false

    @JsonProperty("DAPCostperBag")
    var dapCostPerBag: Double = 0.0

    @JsonProperty("DAPBagWt")
    var dapBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("NPK201010available")
    var npkTwentyAvailable: Boolean = false

    @JsonProperty("NPK201010CostperBag")
    var npkTwentyCostPerBag: Double = 0.0

    @JsonProperty("NPK201010BagWt")
    var npkTwentyBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("NPK151515available")
    var npkFifteenAvailable: Boolean = false

    @JsonProperty("NPK151515CostperBag")
    var npkFifteenCostPerBag: Double = 0.0

    @JsonProperty("NPK151515BagWt")
    var npkFifteenBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("NPK201226available")
    var npkTwentyTwelve26Available: Boolean = false

    @JsonProperty("NPK201226CostperBag")
    var npkTwentyTwelve26CostPerBag: Double = 0.0

    @JsonProperty("NPK201226BagWt")
    var npkTwentyTwelve26BagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("NPK201216available")
    var npkTwentyTwelve16Available: Boolean = false

    @JsonProperty("NPK201216CostperBag")
    var npkTwentyTwelve16CostPerBag: Double = 0.0

    @JsonProperty("NPK201216BagWt")
    var npkTwentyTwelve16BagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("NPK112221available")
    var npkElevenTwentyTwo21Available: Boolean = false

    @JsonProperty("NPK112221CostperBag")
    var npkElevenTwentyTwo21CostPerBag: Double = 0.0

    @JsonProperty("NPK112221BagWt")
    var npkElevenTwentyTwo21BagWeight: Int = DEFAULT_BAG_WEIGHT_INT


    @JsonProperty("NPK251010available")
    var npkTwentyFiveTen10Available: Boolean = false

    @JsonProperty("NPK251010CostperBag")
    var npkTwentyFiveTen10CostPerBag: Double = 0.0

    @JsonProperty("NPK251010BagWt")
    var npkTwentyFiveTen10BagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    /** NEXT fertilizer **/
    @JsonProperty("NPK152020available")
    var npkFifteenTwenty20Available: Boolean = false

    @JsonProperty("NPK152020CostperBag")
    var npkFifteenTwenty20CostPerBag: Double = 0.0

    @JsonProperty("NPK152020BagWt")
    var npkFifteenTwenty20BagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    /** NEXT fertilizer **/
    @JsonProperty("NPK23105available")
    var npkTwentyThreeTen5Available: Boolean = false

    @JsonProperty("NPK23105CostperBag")
    var npkTwentyThreeTen5CostPerBag: Double = 0.0

    @JsonProperty("NPK23105BagWt")
    var npkTwentyThreeTen5BagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    /** NEXT fertilizer **/
    @JsonProperty("NPK123017available")
    var npkTwelveThirty17Available: Boolean = false

    @JsonProperty("NPK123017CostperBag")
    var npkTwelveThirty17CostPerBag: Double = 0.0

    @JsonProperty("NPK123017BagWt")
    var npkTwelveThirty17BagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    /** NEXT fertilizer **/
    @JsonProperty("FOMIBAGARAavailable")
    var fomOneBagaraAvailable: Boolean = false

    @JsonProperty("FOMIBAGARACostperBag")
    var fomOneBagaraCostPerBag: Double = 0.0

    @JsonProperty("FOMIBAGARABagWt")
    var fomOneBagaraBagWeight: Int = DEFAULT_BAG_WEIGHT_INT


    /** NEXT fertilizer **/
    @JsonProperty("FOMIIMBURAavailable")
    var fomTwoMburaAvailable: Boolean = false

    @JsonProperty("FOMIIMBURACostperBag")
    var fomTwoMburaCostPerBag: Double = 0.0

    @JsonProperty("FOMIIMBURABagWt")
    var fomTwoMburaBagWeight: Int = DEFAULT_BAG_WEIGHT_INT


    /** NEXT fertilizer **/
    @JsonProperty("FOMITOTAHAZAavailable")
    var fomOneTotaHazaAvailable: Boolean = false

    @JsonProperty("FOMITOTAHAZACostperBag")
    var fomOneTotaHazCostPerBag: Double = 0.0

    @JsonProperty("FOMITOTAHAZABagWt")
    var fomOneTotaHazBagWeight: Int = DEFAULT_BAG_WEIGHT_INT


    @JsonProperty("TSPavailable")
    var tspAvailable: Boolean = false

    @JsonProperty("TSPCostperBag")
    var tspCostPerBag: Double = 0.0

    @JsonProperty("TSPBagWt")
    var tspBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("NPK171717available")
    var npkSeventeenAvailable: Boolean = false

    @JsonProperty("NPK171717CostperBag")
    var npkSeventeenCostPerBag: Double = 0.0

    @JsonProperty("NPK171717BagWt")
    var npkSeventeenBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("YaraMila_UNIKavailable")
    var yaramilaUnikAvailable: Boolean = false

    @JsonProperty("YaraMila_UNIKCostperBag")
    var yaramilaUnikCostPerBag: Double = 0.0

    @JsonProperty("YaraMila_UNIKBagWt")
    var yaramilaUnikBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("CANavailable")
    var canAvailable: Boolean = false

    @JsonProperty("CANCostperBag")
    var canCostPerBag: Double = 0.0

    @JsonProperty("CANBagWt")
    var canBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

    @JsonProperty("SSPavailable")
    var sspAvailable: Boolean = false

    @JsonProperty("SSPCostperBag")
    var sspCostPerBag: Double = 0.0

    @JsonProperty("SSPBagWt")
    var sspBagWeight: Int = DEFAULT_BAG_WEIGHT_INT

/*
    @JsonProperty("newFert1name")
    var newFert1name: String? = null

    @JsonProperty("newFert1N_cont")
    var newFert1NitrogenContent: String? = null

    @JsonProperty("newFert1P2O5")
    var newFert1PhosphateContent: String? = null
    @JsonProperty("newFertK2O")

    var newFertPotassiumContent: String? = null

    @JsonProperty("newFertCostperBag")
    var newFertCostPerBag: String? = null

    @JsonProperty("newFert1BagWt")
    var newFert1BagWeight: Int = 0

    @JsonProperty("newFert2name")
    var newFert2name: String? = null

    @JsonProperty("newFert2N_cont")
    var newFert2NCont: String? = null

    @JsonProperty("newFert2P2O5")
    var newFert2P2O5: String? = null

    @JsonProperty("newFert2K2O")
    var newFert2K2O: String? = null

    @JsonProperty("newFert2CostperBag")
    var newFert2CostPerBag: String? = null

    @JsonProperty("newFert2BagWt")
    var newFert2BagWeight: Int = 0


    @JsonProperty("newFert3name")
    var newFert3name: String? = null

    @JsonProperty("newFert3N_cont")
    var newFert3NCont: String? = null

    @JsonProperty("newFert3P2O5")
    var newFert3P2O5: String? = null

    @JsonProperty("newFert3K2O")
    var newFert3K2O: String? = null

    @JsonProperty("newFert3CostperBag")
    var newFert3CostPerBag: String? = null

    @JsonProperty("newFert3BagWt")
    var newFert3BagWeight: Int = 0


    @JsonProperty("newFert4name")
    var newFert4name: String? = null

    @JsonProperty("newFert4N_cont")
    var newFert4NCont: String? = null

    @JsonProperty("newFert4P2O5")
    var newFert4P2O5: String? = null

    @JsonProperty("newFert4K2O")
    var newFert4K2O: String? = null

    @JsonProperty("newFert4CostperBag")
    var newFert4CostPerBag: String? = null

    @JsonProperty("newFert4BagWt")
    var newFert4BagWeight: Int = 0


    @JsonProperty("newFert5name")
    var newFert5name: String? = null

    @JsonProperty("newFert5N_cont")
    var newFert5NCont: String? = null

    @JsonProperty("newFert5P2O5")
    var newFert5P2O5: String? = null

    @JsonProperty("newFert5K2O")
    var newFert5K2O: String? = null

    @JsonProperty("newFert5CostperBag")
    var newFert5CostPerBag: String? = null

    @JsonProperty("newFert5BagWt")
    var newFert5BagWeight: Int = 0

     */

    //Debugging flags
    @JsonProperty("debugGH")
    var debugGH: Boolean = false

    @JsonProperty("debugNG")
    var debugNG: Boolean = false

    @JsonProperty("debugTZ")
    var debugTZ: Boolean = false

    @JsonProperty("debugRW")
    var debugRW: Boolean = false
}
