package com.iita.akilimo.core.service

import com.fasterxml.jackson.databind.ObjectMapper
import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.config.PlumberProperties
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.ComputeRequest
import com.iita.akilimo.core.request.FertilizerList
import com.iita.akilimo.core.request.PlumberComputeRequest
import com.iita.akilimo.core.request.RecommendationRequest
import com.iita.akilimo.core.request.usecases.fr.FrRequest
import com.iita.akilimo.core.request.usecases.ic.IcRequest
import com.iita.akilimo.core.request.usecases.bpp.BppRequest
import com.iita.akilimo.database.repos.FertilizerRepo
import com.iita.akilimo.database.entities.Payload
import com.iita.akilimo.database.repos.PayloadRepository
import com.iita.akilimo.enums.EnumCountry
import com.iita.akilimo.enums.EnumFertilizer
import org.joda.time.LocalDateTime
import org.joda.time.Seconds
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate
import java.util.*

@Suppress("UNCHECKED_CAST", "CAST_NEVER_SUCCEEDS", "DuplicatedCode")
@Service
class RecommendationService
constructor(
    val restTemplate: RestTemplate,
    val fertilizerRepo: FertilizerRepo,
    val payloadRepository: PayloadRepository,
    akilimoConfig: AkilimoConfigProperties
) {

    private val logger = LoggerFactory.getLogger(RecommendationService::class.java)

    private val plumberPropertiesProperties: PlumberProperties = akilimoConfig.plumber()
    private val recProperties = akilimoConfig.recommendations()
    private val mapper = ObjectMapper()
    private val modelMapper = ModelMapper()
    private lateinit var recommendationResponseDto: RecommendationResponseDto


    fun computeFrRecommendation(request: FrRequest): RecommendationResponseDto? {
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        val computeRequest = modelMapper.map(recProperties, ComputeRequest::class.java)
        modelMapper.map(request.computeRequest, computeRequest)
        computeRequest.fertilizerRec = true

        //build the recommendation request object now
        val recommendationRequest = RecommendationRequest(
            userInfo = request.userInfo,
            computeRequest = computeRequest,
            fertilizerList = request.fertilizerList
        )
        return computeRecommendations(recommendationRequest)
    }

    fun computeIcRecommendation(request: IcRequest): RecommendationResponseDto? {
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        val computeRequest = modelMapper.map(recProperties, ComputeRequest::class.java)
        modelMapper.map(request.computeRequest, computeRequest)

        computeRequest.interCroppingRec = true
//        computeRequest.intercrop = true

        //build the recommendation request object now
        val recommendationRequest = RecommendationRequest(
            userInfo = request.userInfo,
            computeRequest = computeRequest,
            fertilizerList = request.fertilizerList
        )
        return computeRecommendations(recommendationRequest)
    }

    fun computeSpRecommendation(request: BppRequest): RecommendationResponseDto? {
        //let us map the default values first
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        val computeRequest = modelMapper.map(recProperties, ComputeRequest::class.java)
        modelMapper.map(request.computeRequest, computeRequest)
        computeRequest.scheduledPlantingRec = true
        computeRequest.scheduledHarvestRec = true

        //build the recommendation request object now
        val recommendationRequest = RecommendationRequest(
            userInfo = request.userInfo,
            computeRequest = computeRequest,
            fertilizerList = request.fertilizerList
        )
        return computeRecommendations(recommendationRequest)
    }


    fun computeBppRecommendation(request: BppRequest): RecommendationResponseDto? {
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        val computeRequest = modelMapper.map(recProperties, ComputeRequest::class.java)
        modelMapper.map(request.computeRequest, computeRequest)
        computeRequest.plantingPracticesRec = true

        //build the recommendation request object now
        val recommendationRequest = RecommendationRequest(
            userInfo = request.userInfo,
            computeRequest = computeRequest,
            fertilizerList = request.fertilizerList
        )
        return computeRecommendations(recommendationRequest)
    }


    private fun computeRecommendations(recommendationRequest: RecommendationRequest): RecommendationResponseDto? {
        val countries = ArrayList<String>()
        countries.add(EnumCountry.ALL.name)
        countries.add(recommendationRequest.computeRequest.country)

        var fertilizers = recommendationRequest.fertilizerList

        if (fertilizers.isEmpty() || fertilizers.size < 2) {
            logger.warn("Empty fertilizer list, adding default fertilizers")
            fertilizers = getAvailableFertilizers(countries)
        }

        val fertilizerList = prepareFertilizerList(fertilizers)

        val plumberComputeRequest = this.prepareFertilizerPayload(recommendationRequest, fertilizerList)

        if (recommendationRequest.userInfo.emailAddress.equals("NA", ignoreCase = true)) {
            recommendationRequest.userInfo.sendEmail = false
        }
        val headers = this.setHTTPHeaders()

        val droidRequestString = mapper.writerWithDefaultPrettyPrinter().writeValueAsString(recommendationRequest)
        val plumberRequestString = mapper.writerWithDefaultPrettyPrinter().writeValueAsString(plumberComputeRequest)
        var plumberResponseString = "{}"
        logger.info("Droid payload is: ${recommendationRequest.userInfo.deviceToken}")

        logger.info("Plumber payload username${plumberComputeRequest.userName}")

        val dateTime = LocalDateTime.now()
        try {

            val plumberRequestEntity = HttpEntity(plumberComputeRequest, headers)
            val country = plumberComputeRequest.country

            val baseUrl = plumberPropertiesProperties.baseUrl
            var recommendationUrl: String = "${baseUrl}${plumberPropertiesProperties.computeNg!!}"

            when (country) {
                EnumCountry.NG.name -> recommendationUrl = "${baseUrl}${plumberPropertiesProperties.computeNg!!}"
                EnumCountry.TZ.name -> recommendationUrl = "${baseUrl}${plumberPropertiesProperties.computeTz!!}"
                EnumCountry.GH.name -> recommendationUrl = "${baseUrl}${plumberPropertiesProperties.computeGh!!}"
                EnumCountry.RW.name -> recommendationUrl = "${baseUrl}${plumberPropertiesProperties.computeRw!!}"
            }
            recommendationResponseDto = modelMapper.map(plumberComputeRequest, RecommendationResponseDto::class.java)

            logger.info("Going to endpoint $recommendationUrl at: $dateTime for country $country")

            val response = restTemplate.postForEntity(recommendationUrl, plumberRequestEntity, Array<Any>::class.java)
            val objects = response.body
            plumberResponseString = mapper.writerWithDefaultPrettyPrinter().writeValueAsString(objects)
            if (objects != null) {
                logger.info("Processing plumber response")
                recommendationResponseDto.responsePayload = objects
                when {
                    !processFirstArray(objects) -> {
                        processSecondArray(objects)
                    }
                }
            }
        } catch (ex: Exception) {
            logger.error("An error occurred while processing plumber request ${ex.message}")
        }

        val now = LocalDateTime.now()
        val secondsLapsed = Seconds.secondsBetween(now, dateTime)
        logger.info("Returning response to requesting client $secondsLapsed seconds passed between $dateTime and {$now}")


        //let us save the logged requests
        val payload = Payload()

        payload.requestId = recommendationRequest.userInfo.deviceToken
        payload.droidRequest = droidRequestString
        payload.plumberRequest = plumberRequestString
        payload.plumberResponse = plumberResponseString

        payloadRepository.save(payload)

        return recommendationResponseDto
    }

    private fun processFirstArray(objects: Array<Any>): Boolean {
        try {
            if (objects[0] is LinkedHashMap<*, *>) {
                val computedHashMap = objects[0] as LinkedHashMap<String, ArrayList<Objects>>
                if (computedHashMap.containsKey("FR")) {
                    try {
                        val rec = computedHashMap["FR"] as LinkedHashMap<String, ArrayList<Objects>>
                    } catch (ex: Exception) {
                        logger.error("Error processing linked hash for FR, must be array, going to array next ${ex.message}")
                        val recommendation = computedHashMap["FR"]
                        if (recommendation is ArrayList<*>) {
                            val frText = computedHashMap.getValue("FR") as ArrayList<String>
                            if (frText.size == 1) {
                                recommendationResponseDto.fertilizerRecText = frText[0]
                                recommendationResponseDto.hasResponse = true
                            }
                        }
                    }
                }

                if (computedHashMap.containsKey("SP")) {
                    try {
                        val rec = computedHashMap["SP"] as LinkedHashMap<String, ArrayList<Objects>>
                    } catch (ex: Exception) {
                        logger.error("Error processing linked hash for SP, must be array, going to array next ${ex.message}")
                        val recommendation = computedHashMap["SP"]
                        if (recommendation is ArrayList<*>) {
                            val spText = computedHashMap.getValue("SP") as ArrayList<String>
                            if (spText.size == 1) {
                                recommendationResponseDto.scheduledPlantingRecText = spText[0]
                                recommendationResponseDto.hasResponse = true
                            }
                        }
                    }
                }

                if (computedHashMap.containsKey("PP")) {
                    try {
                        val rec = computedHashMap["PP"] as LinkedHashMap<String, ArrayList<Objects>>
                    } catch (ex: Exception) {
                        logger.error("Error processing linked hash for PP, must be array, going to array next ${ex.message}")
                        val recommendation = computedHashMap["PP"]
                        if (recommendation is ArrayList<*>) {
                            val ppText = computedHashMap.getValue("PP") as ArrayList<String>
                            if (ppText.size == 1) {
                                recommendationResponseDto.plantingPracticeRecText = ppText[0]
                                recommendationResponseDto.hasResponse = true
                            }
                        }
                    }
                }


                if (computedHashMap.containsKey("IC")) {
                    try {
                        val rec = computedHashMap["IC"] as LinkedHashMap<String, ArrayList<Objects>>
                    } catch (ex: Exception) {
                        logger.error("Error processing linked hash for IC, must be array, going to array next ${ex.message}")
                        //check if it is an array
                        val recommendation = computedHashMap["IC"]
                        if (recommendation is ArrayList<*>) {
                            val icText = computedHashMap.getValue("PP") as ArrayList<String>
                            if (icText.size == 1) {
                                recommendationResponseDto.interCroppingRecText = icText[0]
                                recommendationResponseDto.hasResponse = true
                            }
                        }
                    }
                }
            }

        } catch (ex: Exception) {
            recommendationResponseDto.hasResponse = false
            logger.error("An error occurred while processing first array object ${ex.message}")
        }

        return recommendationResponseDto.hasResponse
    }

    private fun processSecondArray(objects: Array<Any>): Boolean {
        try {
            if (objects[1] is LinkedHashMap<*, *>) {
                val recommendationHashMap = objects[1] as LinkedHashMap<String, ArrayList<Objects>>

                if (recommendationHashMap.containsKey("FR")) {
                    val frText = recommendationHashMap.getValue("FR") as ArrayList<String?>
                    recommendationResponseDto.fertilizerRecText = frText[0]
                    recommendationResponseDto.hasResponse = true

                }

                if (recommendationHashMap.containsKey("IC")) {
                    val icText = recommendationHashMap.getValue("IC") as ArrayList<String?>
                    recommendationResponseDto.interCroppingRecText = icText[0]
                    recommendationResponseDto.hasResponse = true
                }

                if (recommendationHashMap.containsKey("PP")) {
                    val ppText = recommendationHashMap.getValue("PP") as ArrayList<String?>
                    recommendationResponseDto.plantingPracticeRecText = ppText[0]
                    recommendationResponseDto.hasResponse = true
                }

                if (recommendationHashMap.containsKey("SP")) {
                    val spText = recommendationHashMap.getValue("SP") as ArrayList<String?>
                    recommendationResponseDto.scheduledPlantingRecText = spText[0]
                    recommendationResponseDto.hasResponse = true
                }
            }
        } catch (ex: Exception) {
            recommendationResponseDto.hasResponse = false
            logger.error("An error occurred while processing first array object ${ex.message}")
        }

        return recommendationResponseDto.hasResponse
    }


    private fun setHTTPHeaders(): HttpHeaders {
        val headers = HttpHeaders()
        headers.add("Content-Type", MediaType.APPLICATION_JSON.toString())
        return headers
    }

    private fun getAvailableFertilizers(countries: ArrayList<String>): Set<FertilizerList> {
        val allFertilizers = fertilizerRepo.findByAvailableIsTrueAndCountryInOrderByNameDesc(countries)
        val availableFertilizers = allFertilizers.map { availableFertilizer ->
            val fertilizerList = FertilizerList(
                fertilizerType = availableFertilizer.fertilizerType!!,
                weight = availableFertilizer.weight!!
            )

            fertilizerList.fertilizerType = availableFertilizer.fertilizerType!!
            fertilizerList
        }
        return availableFertilizers.toSet()
    }


    private fun evaluateFertilizers(tempFertilizerList: LinkedHashMap<String, FertilizerList>): LinkedHashMap<String, FertilizerList> {
        val allFertilizers = fertilizerRepo.findAllByAvailableIsTrue()
        allFertilizers.forEach { fertilizer ->
            if (!tempFertilizerList.containsKey(fertilizer.fertilizerType)) {
                val fertType = fertilizer.fertilizerType!!
                val fertilizerList = FertilizerList(
                    fertilizerType = fertilizer.name!!,
                    weight = 50,
                    price = 0.0
                )

                modelMapper.map(fertilizer, fertilizerList)

                tempFertilizerList[fertType] = fertilizerList
            }
        }
        return tempFertilizerList;
    }

    private fun prepareFertilizerList(fertilizers: Set<FertilizerList>): LinkedHashMap<String, FertilizerList> {
        val fertilizerHashMap = LinkedHashMap<String, FertilizerList>()

        fertilizers.forEach { fertilizer ->
            fertilizer.selected = true
            fertilizerHashMap[fertilizer.fertilizerType] = fertilizer
        }

        return fertilizerHashMap
    }

    private fun prepareFertilizerPayload(
        recommendationRequest: RecommendationRequest,
        tempFertilizerList: LinkedHashMap<String, FertilizerList>
    ): PlumberComputeRequest {
        val modelMapper = ModelMapper()

        val fertilizerList = evaluateFertilizers(tempFertilizerList)
        val requestPayloadPlumber = modelMapper.map(recommendationRequest.userInfo, PlumberComputeRequest::class.java)
        modelMapper.map(recommendationRequest.computeRequest, requestPayloadPlumber)

        //@TODO Make sure area unit is not being passed as translated from the mobile app side
        val areaUnit = requestPayloadPlumber.areaUnits
        if (areaUnit.equals("ekari", false)) {
            requestPayloadPlumber.areaUnits = "acre"
        } else if (areaUnit.equals("hekta", false)) {
            requestPayloadPlumber.areaUnits = "ha"
        }




        if (recommendationRequest.computeRequest.interCroppingMaizeRec || recommendationRequest.computeRequest.interCroppingPotatoRec) {
            requestPayloadPlumber.intercrop = true
            requestPayloadPlumber.interCroppingRec = true
        }
        if (fertilizerList.containsKey(EnumFertilizer.UREA.name)) {
            val urea = fertilizerList[EnumFertilizer.UREA.name]!!
            requestPayloadPlumber.ureaAvailable = urea.selected
            requestPayloadPlumber.ureaBagWeight = urea.weight
            requestPayloadPlumber.ureaCostPerBag = urea.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.CAN.name)) {
            val can = fertilizerList[EnumFertilizer.CAN.name]!!
            requestPayloadPlumber.canAvailable = can.selected
            requestPayloadPlumber.canBagWeight = can.weight
            requestPayloadPlumber.canCostPerBag = can.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.SSP.name)) {
            val can = fertilizerList[EnumFertilizer.SSP.name]!!
            requestPayloadPlumber.sspAvailable = can.selected
            requestPayloadPlumber.sspBagWeight = can.weight
            requestPayloadPlumber.sspCostPerBag = can.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.MOP.name)) {
            val can = fertilizerList[EnumFertilizer.MOP.name]!!
            requestPayloadPlumber.mopAvailable = can.selected
            requestPayloadPlumber.mopBagWeight = can.weight
            requestPayloadPlumber.mopCostPerBag = can.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.DAP.name)) {
            val can = fertilizerList[EnumFertilizer.DAP.name]!!
            requestPayloadPlumber.dapAvailable = can.selected
            requestPayloadPlumber.dapBagWeight = can.weight
            requestPayloadPlumber.dapCostPerBag = can.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.TSP.name)) {
            val can = fertilizerList[EnumFertilizer.TSP.name]!!
            requestPayloadPlumber.tspAvailable = can.selected
            requestPayloadPlumber.tspBagWeight = can.weight
            requestPayloadPlumber.tspCostPerBag = can.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.YARAMILA_UNIK.name)) {
            val yaramilaUnik = fertilizerList[EnumFertilizer.YARAMILA_UNIK.name]!!
            requestPayloadPlumber.yaramilaUnikAvailable = yaramilaUnik.selected
            requestPayloadPlumber.yaramilaUnikBagWeight = yaramilaUnik.weight
            requestPayloadPlumber.yaramilaUnikCostPerBag = yaramilaUnik.price
        }


        if (fertilizerList.containsKey(EnumFertilizer.NPK_20_10_10.name)) {
            val can = fertilizerList[EnumFertilizer.NPK_20_10_10.name]!!
            requestPayloadPlumber.npkTwentyAvailable = can.selected
            requestPayloadPlumber.npkTwentyBagWeight = can.weight
            requestPayloadPlumber.npkTwentyCostPerBag = can.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_17_17_17.name)) {
            val npk17 = fertilizerList[EnumFertilizer.NPK_17_17_17.name]!!
            requestPayloadPlumber.npkSeventeenAvailable = npk17.selected
            requestPayloadPlumber.npkSeventeenBagWeight = npk17.weight
            requestPayloadPlumber.npkSeventeenCostPerBag = npk17.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_15_15_15.name)) {
            val npk15 = fertilizerList[EnumFertilizer.NPK_15_15_15.name]!!
            requestPayloadPlumber.npkFifteenAvailable = npk15.selected
            requestPayloadPlumber.npkFifteenBagWeight = npk15.weight
            requestPayloadPlumber.npkFifteenCostPerBag = npk15.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_20_12_26.name)) {
            val npk20 = fertilizerList[EnumFertilizer.NPK_20_12_26.name]!!
            requestPayloadPlumber.npkTwentyTwelve26Available = npk20.selected
            requestPayloadPlumber.npkTwentyTwelve26BagWeight = npk20.weight
            requestPayloadPlumber.npkTwentyTwelve26CostPerBag = npk20.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_20_12_16.name)) {
            val npk201216 = fertilizerList[EnumFertilizer.NPK_20_12_16.name]!!
            requestPayloadPlumber.npkTwentyTwelve16Available = npk201216.selected
            requestPayloadPlumber.npkTwentyTwelve16BagWeight = npk201216.weight
            requestPayloadPlumber.npkTwentyTwelve16CostPerBag = npk201216.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_11_22_21.name)) {
            val npk112221 = fertilizerList[EnumFertilizer.NPK_11_22_21.name]!!
            requestPayloadPlumber.npkElevenTwentyTwo21Available = npk112221.selected
            requestPayloadPlumber.npkElevenTwentyTwo21BagWeight = npk112221.weight
            requestPayloadPlumber.npkElevenTwentyTwo21CostPerBag = npk112221.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_25_10_10.name)) {
            val npk251010 = fertilizerList[EnumFertilizer.NPK_25_10_10.name]!!
            requestPayloadPlumber.npkTwentyFiveTen10Available = npk251010.selected
            requestPayloadPlumber.npkTwentyFiveTen10BagWeight = npk251010.weight
            requestPayloadPlumber.npkTwentyFiveTen10CostPerBag = npk251010.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_15_20_20.name)) {
            val npk152020 = fertilizerList[EnumFertilizer.NPK_15_20_20.name]!!
            requestPayloadPlumber.npkFifteenTwenty20Available = npk152020.selected
            requestPayloadPlumber.npkFifteenTwenty20BagWeight = npk152020.weight
            requestPayloadPlumber.npkFifteenTwenty20CostPerBag = npk152020.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_23_10_5.name)) {
            val npk23105 = fertilizerList[EnumFertilizer.NPK_23_10_5.name]!!
            requestPayloadPlumber.npkTwentyThreeTen5Available = npk23105.selected
            requestPayloadPlumber.npkTwentyThreeTen5BagWeight = npk23105.weight
            requestPayloadPlumber.npkTwentyThreeTen5CostPerBag = npk23105.price
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK_12_30_17.name)) {
            val npk123017 = fertilizerList[EnumFertilizer.NPK_12_30_17.name]!!
            requestPayloadPlumber.npkTwelveThirty17Available = npk123017.selected
            requestPayloadPlumber.npkTwelveThirty17BagWeight = npk123017.weight
            requestPayloadPlumber.npkTwelveThirty17CostPerBag = npk123017.price
        }

        return requestPayloadPlumber
    }

}
