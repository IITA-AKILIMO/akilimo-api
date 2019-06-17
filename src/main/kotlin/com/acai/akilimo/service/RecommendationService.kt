package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.ComputeRequest
import com.acai.akilimo.entities.Recommendation
import com.acai.akilimo.entities.Response
import com.acai.akilimo.enum.EnumFertilizer
import com.acai.akilimo.repositories.RecommendationRepository
import com.acai.akilimo.interfaces.IRecommendationService
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.PlumberProperties
import com.acai.akilimo.entities.FertilizerList
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate
import java.util.*
import com.fasterxml.jackson.databind.ObjectMapper
import org.joda.time.LocalDateTime
import org.joda.time.Seconds
import org.modelmapper.ModelMapper
import kotlin.collections.ArrayList
import java.util.LinkedHashMap


@Suppress("UNCHECKED_CAST")
@Service
class RecommendationService
@Autowired
constructor(private val recommendationRepository: RecommendationRepository, private val restTemplate: RestTemplate, akilimoConfigProperties: AkilimoConfigProperties) : IRecommendationService {

    private val logger = LoggerFactory.getLogger(IRecommendationService::class.java)

    private val plumberPropertiesProperties: PlumberProperties = akilimoConfigProperties.plumber()

    override fun listAllRequests(): List<Recommendation> {
        return recommendationRepository.findAll()
    }

    override fun saveRecommendationRequest(recommendation: Recommendation): Recommendation? {
        try {
            val fertilizerList = recommendation.addFertilizers(recommendation)

            recommendation.fertilizers = fertilizerList

            logger.info("Logging requests for fertilizer recommendations", 6)

            val savedRequest = recommendationRepository.save(recommendation)

            val computed = this.sendToComputeTool(savedRequest)

            savedRequest.recommendationText = computed?.fertilizerRecText;

            return savedRequest
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        return null
    }


    fun prepareFertilizerList(fertilizers: Set<FertilizerList>): LinkedHashMap<String, FertilizerList> {
        val fertilizerHashMap = LinkedHashMap<String, FertilizerList>()

        fertilizers.forEach { fertilizer ->
            fertilizerHashMap[fertilizer.fertilizerType!!] = fertilizer
        }

        return fertilizerHashMap
    }

    fun computeRecommendations(computeRequest: ComputeRequest, fertilizerList: LinkedHashMap<String, FertilizerList>): RecommendationResponseDto? {
        var recommendationResponseDto: RecommendationResponseDto? = null

        // val requestPayload = computeRequest;
        //prepare the fertillizers
        val requestPayload = this.prepareFertilizerPayload(computeRequest, fertilizerList)


        val headers = this.setHTTPHeaders()

        logger.info("Payload is $requestPayload")
        //logger.info("Request has entered here, proceeding " + computeRequest.harvestDate!!)

        val mapper = ObjectMapper()
        val modelMapper = ModelMapper()
        //send to plumber
        val dateTime = LocalDateTime.now()
        try {
            val jsonString = mapper.writeValueAsString(requestPayload)

            logger.info("Plumber payload: $jsonString")

            val entity = HttpEntity(computeRequest, headers)
            val fertilizerRecommendationUrl = plumberPropertiesProperties.baseUrl + plumberPropertiesProperties.fertilizerRecommendation!!

            recommendationResponseDto = modelMapper.map(computeRequest, RecommendationResponseDto::class.java)

            logger.info("Going to endpoint $fertilizerRecommendationUrl at: $dateTime")


            //val response = restTemplate.postForEntity(fertilizerRecommendationUrl, jsonString, Array<Any>::class.java)

            val response = restTemplate.postForEntity(fertilizerRecommendationUrl, entity, Array<Any>::class.java)

            val objects = response.body

            if (objects != null) {

                if (objects[0] is LinkedHashMap<*, *>) {
                    val computedHashMap = objects[0] as LinkedHashMap<String, ArrayList<Objects>>
                    /*val computedData = objects[0] as ArrayList<Objects>
                        val usercomputedData = objects[1] as ArrayList<Objects>
                        val fertilizerRecText = objects[2] as ArrayList<Objects>
                        val values = mapper.readValue(mapper.writeValueAsString(computedData), Array<Response>::class.java)
                    */

                    if (computedHashMap.containsKey("FR")) {
                        //extract the fertilizer recommendations
                        try {
                            val rec = computedHashMap["FR"] as LinkedHashMap<String, ArrayList<Objects>>
                        } catch (ex: Exception) {
                            logger.error("Error processing linked hash, must be array, going to array next ${ex.message}")
                            //check if it is an array
                            val recommendation = computedHashMap["FR"]
                            if (recommendation is ArrayList<*>) {
                                //this indicates the responses has a message that needs to be processed
                                val frText = computedHashMap.getValue("FR") as ArrayList<String>
                                recommendationResponseDto.fertilizerRecText = frText[0]
                                recommendationResponseDto.hasResponse = true
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
        logger.info("Returning response to requesting client seconds $secondsLapsed passed between $dateTime and {$now}")


        return recommendationResponseDto
    }


    @Deprecated("This function is subject to modification")
    private fun sendToComputeTool(recommendation: Recommendation): RecommendationResponseDto? {
        val recommendationResponseDto = RecommendationResponseDto()

        val headers = this.setHTTPHeaders()

        logger.info("Payload is $recommendation")
        logger.info("Request has entered here, proceeding " + recommendation.harvestDate!!)

        val mapper = ObjectMapper()
        val modelMapper = ModelMapper()
        //send to plumber
        try {
            val entity = HttpEntity(recommendation, headers)
            val fertilizerRecommendationUrl = plumberPropertiesProperties.baseUrl + plumberPropertiesProperties.fertilizerRecommendation!!

            logger.info("Going to endpoint $fertilizerRecommendationUrl")
            val response = restTemplate.postForEntity(
                    fertilizerRecommendationUrl, entity, Array<Any>::class.java)

            val objects = response.body

            if (objects != null) {

                val computedData = objects[0] as ArrayList<Objects>
                val usercomputedData = objects[1] as ArrayList<Objects>
                val recommendationText = objects[2] as ArrayList<Objects>

                val values = mapper.readValue(mapper.writeValueAsString(computedData), Array<Response>::class.java)


                recommendationResponseDto.fertilizerRecText = objects[2].toString()
            }
        } catch (ex: Exception) {
            logger.error("An error occurred " + ex.message)
        }

        logger.info("Returning response to requesting client")

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

        //clear the fertilizer list

        return requestPayload
    }
}
