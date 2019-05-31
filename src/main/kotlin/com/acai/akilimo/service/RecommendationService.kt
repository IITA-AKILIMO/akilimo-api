package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.entities.RecommendationResponse
import com.acai.akilimo.repositories.RecommendationRepository
import com.acai.akilimo.interfaces.IRecommendationService
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.PlumberProperties
import com.acai.akilimo.request.ComputeRequest
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


@Suppress("UNCHECKED_CAST")
@Service
class RecommendationService
@Autowired
constructor(private val recommendationRepository: RecommendationRepository, private val restTemplate: RestTemplate, akilimoConfigProperties: AkilimoConfigProperties) : IRecommendationService {

    private val logger = LoggerFactory.getLogger(IRecommendationService::class.java)

    private val plumberPropertiesProperties: PlumberProperties = akilimoConfigProperties.plumber()

    override fun listAllRequests(): List<RecommendationRequest> {
        return recommendationRepository.findAll()
    }

    override fun saveRecommendationRequest(recommendationRequest: RecommendationRequest): RecommendationRequest? {
        try {
            val fertilizerList = recommendationRequest.addFertilizers(recommendationRequest)

            recommendationRequest.fertilizers = fertilizerList

            logger.info("Logging requests for fertilizer recommendations", 6)

            val savedRequest = recommendationRepository.save(recommendationRequest)

            val computed = this.sendToComputeTool(savedRequest)

            savedRequest.recommendationText = computed?.fertilizerRecText;

            return savedRequest
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        return null
    }


    fun computeRecommendations(computeRequest: ComputeRequest): RecommendationResponseDto? {
        var recommendationResponseDto: RecommendationResponseDto? = null

        val headers = this.setHTTPHeaders()

        logger.info("Payload is $computeRequest")
        //logger.info("Request has entered here, proceeding " + computeRequest.harvestDate!!)

        val mapper = ObjectMapper()
        val modelMapper = ModelMapper()
        //send to plumber
        val dateTime = LocalDateTime.now()
        try {
            val entity = HttpEntity(computeRequest, headers)
            val fertilizerRecommendationUrl = plumberPropertiesProperties.baseUrl + plumberPropertiesProperties.fertilizerRecommendation!!

            recommendationResponseDto = modelMapper.map(computeRequest, RecommendationResponseDto::class.java)

            logger.info("Going to endpoint $fertilizerRecommendationUrl at: $dateTime")

            val response = restTemplate.postForEntity(fertilizerRecommendationUrl, entity, Array<Any>::class.java)

            val objects = response.body


            if (objects != null) {

                val computedHashMap = objects[0] as LinkedHashMap<String, ArrayList<Objects>>
                val recommendationHashMap = objects[1] as LinkedHashMap<String, ArrayList<Objects>>

                /*val computedData = objects[0] as ArrayList<Objects>
                    val usercomputedData = objects[1] as ArrayList<Objects>
                    val fertilizerRecText = objects[2] as ArrayList<Objects>
                    val values = mapper.readValue(mapper.writeValueAsString(computedData), Array<RecommendationResponse>::class.java)
                */

                if (computedHashMap.containsKey("FR")) {
                    //extract the fertilizer recommendations
                    val recommendation = computedHashMap.get("rec")
                }

                if (recommendationHashMap.containsKey("FR")) {
                    val frText = recommendationHashMap.getValue("FR") as ArrayList<String?>
                    recommendationResponseDto.fertilizerRecText = frText[0]
                }

                if (recommendationHashMap.containsKey("IC")) {
                    val icText = recommendationHashMap.getValue("IC") as ArrayList<String?>
                    recommendationResponseDto.interCroppingRecText = icText[0]
                }

                if (recommendationHashMap.containsKey("PP")) {
                    val ppText = recommendationHashMap.getValue("PP") as ArrayList<String?>
                    recommendationResponseDto.plantingPracticeRecText = ppText[0]
                }

                if (recommendationHashMap.containsKey("SP")) {
                    val spText = recommendationHashMap.getValue("SP") as ArrayList<String?>
                    recommendationResponseDto.scheduledPlantingRecText = spText[0]
                }
            }

        } catch (ex: Exception) {
            logger.error("An error occurred " + ex.message)
        }

        val now = LocalDateTime.now()
        val secondsLapsed = Seconds.secondsBetween(now, dateTime)
        logger.info("Returning response to requesting client seconds $secondsLapsed passed between $dateTime and {$now}")

        //perhaps we should send sms now?
        return recommendationResponseDto
    }

    @Deprecated("This function is subject to modification")
    private fun sendToComputeTool(recommendationRequest: RecommendationRequest): RecommendationResponseDto? {
        val recommendationResponseDto = RecommendationResponseDto()

        val headers = this.setHTTPHeaders()

        logger.info("Payload is $recommendationRequest")
        logger.info("Request has entered here, proceeding " + recommendationRequest.harvestDate!!)

        val mapper = ObjectMapper()
        val modelMapper = ModelMapper()
        //send to plumber
        try {
            val entity = HttpEntity(recommendationRequest, headers)
            val fertilizerRecommendationUrl = plumberPropertiesProperties.baseUrl + plumberPropertiesProperties.fertilizerRecommendation!!

            logger.info("Going to endpoint $fertilizerRecommendationUrl")
            val response = restTemplate.postForEntity(
                    fertilizerRecommendationUrl, entity, Array<Any>::class.java)

            val objects = response.body

            if (objects != null) {

                val computedData = objects[0] as ArrayList<Objects>
                val usercomputedData = objects[1] as ArrayList<Objects>
                val recommendationText = objects[2] as ArrayList<Objects>

                val values = mapper.readValue(mapper.writeValueAsString(computedData), Array<RecommendationResponse>::class.java)


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
}
