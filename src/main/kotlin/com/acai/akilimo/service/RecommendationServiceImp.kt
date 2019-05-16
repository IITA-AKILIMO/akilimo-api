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

            logger.info("Logging requests for fertilizer recommendations",6)
            return recommendationRepository.save(recommendationRequest)
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        return null
    }

    override fun sendToComputeTool(recommendationRequest: RecommendationRequest): RecommendationResponseDto? {
        val recommendationResponseDto = RecommendationResponseDto()

        val headers = HttpHeaders()
        headers.add("Content-Type", MediaType.APPLICATION_JSON.toString())

        logger.info("Payload is $recommendationRequest")
        logger.info("Request has entered here, proceeding " + recommendationRequest.harvestDate!!)
        //send to plumber
        val entity = HttpEntity(recommendationRequest, headers)
        val fertilizerRecommendationUrl = plumberProperties.fertilizerRecommendation!!

        logger.info("Going to endpoint $fertilizerRecommendationUrl")
        val response = restTemplate.postForEntity(
                fertilizerRecommendationUrl, entity, Array<RecommendationResponse>::class.java)

        val objects = response.body

        if (objects != null) {
            recommendationResponseDto.recommendationText = objects[2].toString()
        }

        logger.info("Returning response to requesting client")

        return recommendationResponseDto
    }
}
