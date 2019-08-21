package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.request.ComputeRequest
import com.acai.akilimo.request.FertilizerList
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.enums.EnumFertilizer
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.PlumberProperties
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


@Suppress("UNCHECKED_CAST", "CAST_NEVER_SUCCEEDS")
@Service
class RecommendationService
@Autowired
constructor(private val restTemplate: RestTemplate, akilimoConfigProperties: AkilimoConfigProperties) {

    private val logger = LoggerFactory.getLogger(RecommendationService::class.java)

    private val plumberPropertiesProperties: PlumberProperties = akilimoConfigProperties.plumber()


    fun prepareFertilizerList(fertilizers: Set<FertilizerList>): LinkedHashMap<String, FertilizerList> {
        val fertilizerHashMap = LinkedHashMap<String, FertilizerList>()

        fertilizers.forEach { fertilizer ->
            fertilizerHashMap[fertilizer.fertilizerType!!] = fertilizer
        }

        return fertilizerHashMap
    }

    fun computeRecommendations(computeRequest: ComputeRequest, fertilizerList: LinkedHashMap<String, FertilizerList>): RecommendationResponseDto? {
        var recommendationResponseDto: RecommendationResponseDto? = null
        val mapper = ObjectMapper()
        val modelMapper = ModelMapper()
        val requestPayload = this.prepareFertilizerPayload(computeRequest, fertilizerList)


        val headers = this.setHTTPHeaders()

        logger.info("Plumber payload is")
        logger.info(mapper.writerWithDefaultPrettyPrinter().writeValueAsString(requestPayload))


        //send to plumber
        val dateTime = LocalDateTime.now()
        try {

            val entity = HttpEntity(requestPayload, headers)
            val country = computeRequest.country

            var recommendationUrl: String? = null
            when (country) {
                EnumCountry.NG.name ->
                    recommendationUrl = "${plumberPropertiesProperties.baseUrl}${plumberPropertiesProperties.recommendationNg!!}"
                EnumCountry.TZ.name ->
                    recommendationUrl = "${plumberPropertiesProperties.baseUrl}${plumberPropertiesProperties.recommendationTz!!}"
            }
            recommendationResponseDto = modelMapper.map(requestPayload, RecommendationResponseDto::class.java)

            logger.info("Going to endpoint $recommendationUrl at: $dateTime")

            val response = restTemplate.postForEntity(recommendationUrl!!, entity, Array<Any>::class.java)

            val objects = response.body

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


    private fun prepareFertilizerPayload(requestPayload: ComputeRequest, fertilizerList: LinkedHashMap<String, FertilizerList>): ComputeRequest {
        if (fertilizerList.containsKey(EnumFertilizer.UREA.name)) {
            val urea = fertilizerList[EnumFertilizer.UREA.name]!!
            requestPayload.ureaAvailable = urea.available!!
            requestPayload.ureaBagWeight = urea.fertilizerWeight!!
            requestPayload.ureaCostPerBag = urea.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.CAN.name)) {
            val can = fertilizerList[EnumFertilizer.CAN.name]!!
            requestPayload.canAvailable = can.available!!
            requestPayload.canBagWeight = can.fertilizerWeight!!
            requestPayload.canCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.SSP.name)) {
            val can = fertilizerList[EnumFertilizer.SSP.name]!!
            requestPayload.sspAvailable = can.available!!
            requestPayload.sspBagWeight = can.fertilizerWeight!!
            requestPayload.sspCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.MOP.name)) {
            val can = fertilizerList[EnumFertilizer.MOP.name]!!
            requestPayload.mopAvailable = can.available!!
            requestPayload.mopBagWeight = can.fertilizerWeight!!
            requestPayload.mopCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.DAP.name)) {
            val can = fertilizerList[EnumFertilizer.DAP.name]!!
            requestPayload.dapAvailable = can.available!!
            requestPayload.dapBagWeight = can.fertilizerWeight!!
            requestPayload.dapCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.TSP.name)) {
            val can = fertilizerList[EnumFertilizer.TSP.name]!!
            requestPayload.tspAvailable = can.available!!
            requestPayload.tspBagWeight = can.fertilizerWeight!!
            requestPayload.tspCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.NAFAKAPLUS.name)) {
            val can = fertilizerList[EnumFertilizer.NAFAKAPLUS.name]!!
            requestPayload.nafakaAvailable = can.available!!
            requestPayload.nafakaBagWeight = can.fertilizerWeight!!
            requestPayload.nafakaCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.YARAMILA_UNIK.name)) {
            val yaramilaUnik = fertilizerList[EnumFertilizer.YARAMILA_UNIK.name]!!
            requestPayload.yaramilaUnikAvailable = yaramilaUnik.available!!
            requestPayload.yaramilaUnikBagWeight = yaramilaUnik.fertilizerWeight!!
            requestPayload.yaramilaUnikCostPerBag = yaramilaUnik.fertilizerCostPerBag!!
        }


        if (fertilizerList.containsKey(EnumFertilizer.NPK20_10_10.name)) {
            val can = fertilizerList[EnumFertilizer.NPK20_10_10.name]!!
            requestPayload.npkTwentyAvailable = can.available!!
            requestPayload.npkTwentyBagWeight = can.fertilizerWeight!!
            requestPayload.npkTwentyCostPerBag = can.fertilizerCostPerBag!!
        }


        if (fertilizerList.containsKey(EnumFertilizer.NPK17_17_17.name)) {
            val can = fertilizerList[EnumFertilizer.NPK17_17_17.name]!!
            requestPayload.npkSeventeenAvailable = can.available!!
            requestPayload.npkSeventeenBagWeight = can.fertilizerWeight!!
            requestPayload.npkSeventeenCostPerBag = can.fertilizerCostPerBag!!
        }

        if (fertilizerList.containsKey(EnumFertilizer.NPK15_15_15.name)) {
            val can = fertilizerList[EnumFertilizer.NPK15_15_15.name]!!
            requestPayload.npkFifteenAvailable = can.available!!
            requestPayload.npkFifteenBagWeight = can.fertilizerWeight!!
            requestPayload.npkFifteenCostPerBag = can.fertilizerCostPerBag!!
        }


        val fertilizerOneNames = EnumFertilizer.CUSTOM_FERT_ONE.fertilizerKey

        fertilizerOneNames.forEach { fertNameKey ->

            if (fertilizerList.containsKey(fertNameKey)) {
                val customFertilizerOne = fertilizerList[fertNameKey]!!
                requestPayload.newFert1name = customFertilizerOne.fertilizerTypeName!!
                requestPayload.newFert1BagWeight = customFertilizerOne.fertilizerWeight!!
                requestPayload.newFertCostPerBag = customFertilizerOne.fertilizerCostPerBag!!
                requestPayload.newFert1NitrogenContent = customFertilizerOne.nitrogenContent
                requestPayload.newFert1PhosphateContent = customFertilizerOne.phosphateContent
                requestPayload.newFertPotassiumContent = customFertilizerOne.potassiumContent
            }

        }

        return requestPayload
    }
}
