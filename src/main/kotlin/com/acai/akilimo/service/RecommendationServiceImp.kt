package com.acai.akilimo.service

import com.acai.akilimo.config.ConfigProperties
import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.entities.RecommendationResponse
import com.acai.akilimo.repositories.RecommendationRepository
import com.acai.akilimo.interfaces.IRecommendationService
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.Plumber
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate
import org.springframework.http.ResponseEntity
import java.util.*
import com.fasterxml.jackson.databind.ObjectMapper
import org.modelmapper.ModelMapper
import kotlin.collections.ArrayList
import org.springframework.http.converter.json.Jackson2ObjectMapperBuilder.json
import org.h2.value.DataType.readValue




@Service
class RecommendationServiceImp @Autowired
constructor(private val recommendationRepository: RecommendationRepository, private val restTemplate: RestTemplate, configProperties: ConfigProperties) : IRecommendationService {

    private val logger = LoggerFactory.getLogger(IRecommendationService::class.java)

    private val plumberProperties: Plumber = configProperties.plumber()

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

            return savedRequest
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        return null
    }

    private fun sendToComputeTool(recommendationRequest: RecommendationRequest): RecommendationResponseDto? {
        val recommendationResponseDto = RecommendationResponseDto()

        val headers = HttpHeaders()
        headers.add("Content-Type", MediaType.APPLICATION_JSON.toString())

        logger.info("Payload is $recommendationRequest")
        logger.info("Request has entered here, proceeding " + recommendationRequest.harvestDate!!)

        val mapper = ObjectMapper()
        val modelMapper = ModelMapper()
        //send to plumber
        try {
            val entity = HttpEntity(recommendationRequest, headers)
            val fertilizerRecommendationUrl = plumberProperties.fertilizerRecommendation!!

            logger.info("Going to endpoint $fertilizerRecommendationUrl")
            val response = restTemplate.postForEntity(
                    fertilizerRecommendationUrl, entity, Array<Any>::class.java)

            val objects = response.body

            if (objects != null) {

                val computedData= objects[0] as ArrayList<Objects>
                val usercomputedData = objects[1]  as ArrayList<Objects>
                val recommendationText = objects[2]  as ArrayList<Objects>

                val values = mapper.readValue(mapper.writeValueAsString(computedData), Array<RecommendationResponse>::class.java)


                recommendationResponseDto.recommendationText = objects[2].toString()
            }
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        logger.info("Returning response to requesting client")

        return recommendationResponseDto
    }
}
