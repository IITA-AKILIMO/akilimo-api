package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.enums.EnumFertilizer
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.PlumberProperties
import com.acai.akilimo.request.FertilizerList
import com.acai.akilimo.request.PlumberComputeRequest
import com.acai.akilimo.request.RecommendationRequest
import com.fasterxml.jackson.databind.ObjectMapper
import org.joda.time.LocalDateTime
import org.joda.time.Seconds
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate
import java.util.*

@Suppress("UNCHECKED_CAST", "CAST_NEVER_SUCCEEDS", "DuplicatedCode")
@Service
class RecommendationService
@Autowired
constructor(private val restTemplate: RestTemplate, akilimoConfigProperties: AkilimoConfigProperties) {

    private val logger = LoggerFactory.getLogger(RecommendationService::class.java)

    private val plumberPropertiesProperties: PlumberProperties = akilimoConfigProperties.plumber()
    private val mapper = ObjectMapper()
    private val modelMapper = ModelMapper()

    fun computeRecommendations(recommendationRequest: RecommendationRequest): RecommendationResponseDto? {
        var recommendationResponseDto: RecommendationResponseDto? = null


        val fertilizerList = prepareFertilizerList(recommendationRequest.fertilizerList)
        val plumberComputeRequest = this.prepareFertilizerPayload(recommendationRequest, fertilizerList)
        val headers = this.setHTTPHeaders()

        logger.info("Plumber payload is")
        logger.info(mapper.writerWithDefaultPrettyPrinter().writeValueAsString(plumberComputeRequest))

        val dateTime = LocalDateTime.now()
        try {

            val entity = HttpEntity(plumberComputeRequest, headers)
            val country = plumberComputeRequest.country
            val demoMode = plumberPropertiesProperties.demoMode
            var recommendationUrl: String? = null

            when (country) {
                EnumCountry.NG.name -> recommendationUrl = when {
                    demoMode -> "${plumberPropertiesProperties.baseUrl}${plumberPropertiesProperties.recommendationNgDemo!!}"
                    else -> "${plumberPropertiesProperties.baseUrl}${plumberPropertiesProperties.recommendationNg!!}"
                }
                EnumCountry.TZ.name -> recommendationUrl = when {
                    demoMode -> "${plumberPropertiesProperties.baseUrl}${plumberPropertiesProperties.recommendationTzDemo!!}"
                    else -> "${plumberPropertiesProperties.baseUrl}${plumberPropertiesProperties.recommendationTz!!}"
                }
            }
            recommendationResponseDto = modelMapper.map(plumberComputeRequest, RecommendationResponseDto::class.java)

            logger.info("Going to endpoint $recommendationUrl at: $dateTime")

            val response = restTemplate.postForEntity(recommendationUrl!!, entity, Array<Any>::class.java)

            val objects = response.body

            logger.info("Plumber payload response is")
            logger.info(mapper.writerWithDefaultPrettyPrinter().writeValueAsString(objects))

            if (objects != null) {

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
            }

        } catch (ex: Exception) {
            logger.error("An error occurred " + ex.message)
            recommendationResponseDto?.hasResponse = false
        }

        val now = LocalDateTime.now()
        val secondsLapsed = Seconds.secondsBetween(now, dateTime)
        logger.info("Returning response to requesting client $secondsLapsed seconds passed between $dateTime and {$now}")


        return recommendationResponseDto
    }


    private fun setHTTPHeaders(): HttpHeaders {
        val headers = HttpHeaders()
        headers.add("Content-Type", MediaType.APPLICATION_JSON.toString())
        return headers
    }

    private fun prepareFertilizerList(fertilizers: Set<FertilizerList>): LinkedHashMap<String, FertilizerList> {
        val fertilizerHashMap = LinkedHashMap<String, FertilizerList>()

        fertilizers.forEach { fertilizer ->
            fertilizerHashMap[fertilizer.fertilizerType!!] = fertilizer
        }

        return fertilizerHashMap
    }


    private fun prepareFertilizerPayload(recommendationRequest: RecommendationRequest, fertilizerList: LinkedHashMap<String, FertilizerList>): PlumberComputeRequest {
        val modelMapper = ModelMapper()

        val requestPayloadPlumber = modelMapper.map(recommendationRequest.userInfo, PlumberComputeRequest::class.java)
        modelMapper.map(recommendationRequest.computeRequest, requestPayloadPlumber)

        if (fertilizerList.containsKey(EnumFertilizer.UREA.name)) {
            val urea = fertilizerList[EnumFertilizer.UREA.name]!!
            requestPayloadPlumber.ureaAvailable = urea.available!!
            requestPayloadPlumber.ureaBagWeight = urea.fertilizerWeight!!
            requestPayloadPlumber.ureaCostPerBag = urea.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.CAN.name)) {
            val can = fertilizerList[EnumFertilizer.CAN.name]!!
            requestPayloadPlumber.canAvailable = can.available!!
            requestPayloadPlumber.canBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.canCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.SSP.name)) {
            val can = fertilizerList[EnumFertilizer.SSP.name]!!
            requestPayloadPlumber.sspAvailable = can.available!!
            requestPayloadPlumber.sspBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.sspCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.MOP.name)) {
            val can = fertilizerList[EnumFertilizer.MOP.name]!!
            requestPayloadPlumber.mopAvailable = can.available!!
            requestPayloadPlumber.mopBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.mopCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.DAP.name)) {
            val can = fertilizerList[EnumFertilizer.DAP.name]!!
            requestPayloadPlumber.dapAvailable = can.available!!
            requestPayloadPlumber.dapBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.dapCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.TSP.name)) {
            val can = fertilizerList[EnumFertilizer.TSP.name]!!
            requestPayloadPlumber.tspAvailable = can.available!!
            requestPayloadPlumber.tspBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.tspCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.NAFAKAPLUS.name)) {
            val can = fertilizerList[EnumFertilizer.NAFAKAPLUS.name]!!
            requestPayloadPlumber.nafakaAvailable = can.available!!
            requestPayloadPlumber.nafakaBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.nafakaCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.YARAMILA_UNIK.name)) {
            val yaramilaUnik = fertilizerList[EnumFertilizer.YARAMILA_UNIK.name]!!
            requestPayloadPlumber.yaramilaUnikAvailable = yaramilaUnik.available!!
            requestPayloadPlumber.yaramilaUnikBagWeight = yaramilaUnik.fertilizerWeight!!
            requestPayloadPlumber.yaramilaUnikCostPerBag = yaramilaUnik.fertilizerCostPerBag!!
        }


        if (fertilizerList.containsKey(EnumFertilizer.NPK20_10_10.name)) {
            val can = fertilizerList[EnumFertilizer.NPK20_10_10.name]!!
            requestPayloadPlumber.npkTwentyAvailable = can.available!!
            requestPayloadPlumber.npkTwentyBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.npkTwentyCostPerBag = can.fertilizerCostPerBag!!
        }


        if (fertilizerList.containsKey(EnumFertilizer.NPK17_17_17.name)) {
            val can = fertilizerList[EnumFertilizer.NPK17_17_17.name]!!
            requestPayloadPlumber.npkSeventeenAvailable = can.available!!
            requestPayloadPlumber.npkSeventeenBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.npkSeventeenCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK15_15_15.name)) {
            val can = fertilizerList[EnumFertilizer.NPK15_15_15.name]!!
            requestPayloadPlumber.npkFifteenAvailable = can.available!!
            requestPayloadPlumber.npkFifteenBagWeight = can.fertilizerWeight!!
            requestPayloadPlumber.npkFifteenCostPerBag = can.fertilizerCostPerBag!!
        }

        when (requestPayloadPlumber.country) {
            EnumCountry.NG.name -> {
                requestPayloadPlumber.mapLat = 8.725
                requestPayloadPlumber.mapLong = 4.025
            }
            EnumCountry.TZ.name -> {
                requestPayloadPlumber.mapLat = -7.725
                requestPayloadPlumber.mapLong = 37.875
            }
        }

        when (requestPayloadPlumber.cassavaProduceType) {
            "NA" -> requestPayloadPlumber.cassavaProduceType = "root"
        }

        when (requestPayloadPlumber.cassavaUnitWeight) {
            0 -> requestPayloadPlumber.cassavaUnitWeight = 50
        }

        return requestPayloadPlumber
    }

}
