package com.acai.akilimo.controllers


import com.acai.akilimo.config.ConfigProperties
import com.acai.akilimo.entities.RecommendationResponse
import com.acai.akilimo.properties.Plumber
import com.acai.akilimo.service.RecommendationServiceImp
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.web.bind.annotation.*
import org.springframework.web.client.RestTemplate

@RequestMapping("/api/v2/recommendations")
@RestController
class RecommendationsController @Autowired
constructor(private val yieldRequestServiceImp: RecommendationServiceImp, private val restTemplate: RestTemplate, configProperties: ConfigProperties) {

    private val logger = LoggerFactory.getLogger(RecommendationsController::class.java)


    private val plumberProperties: Plumber = configProperties.plumber()

    @GetMapping
    fun listYieldRequests(): List<com.acai.akilimo.entities.RecommendationRequest> {
        return yieldRequestServiceImp.findAll()
    }

    @PostMapping("/compute")
    fun processYieldRequest(@RequestBody recommendationRequest: com.acai.akilimo.entities.RecommendationRequest?): com.acai.akilimo.entities.RecommendationRequest? {
            return  yieldRequestServiceImp.saveRecommendationRequest(recommendationRequest!!)
    }

    @PostMapping("/compute/direct")
    fun processDirectYieldRequest(@RequestBody recommendationRequest: RecommendationRequest): RecommendationResponse {
        val yieldResponse = RecommendationResponse()
        val headers = HttpHeaders()

        headers.add("Content-Type", MediaType.APPLICATION_JSON.toString())

        logger.info("Payload is $recommendationRequest")
        logger.info("Request has entered here, proceeding " + recommendationRequest.harvestDate!!)
        if (recommendationRequest != null) {
            //send to plumber
            val entity = HttpEntity(recommendationRequest, headers)
            val theUrl = plumberProperties.endpoint!! + "/estimate/compute"

            logger.info("Going to endpoint $theUrl")
            val response = restTemplate.postForEntity(
                    theUrl, entity, Array<RecommendationResponse>::class.java)

            val objects = response.body

            if (objects != null) {
                yieldResponse.reccomendationText = objects[2].toString()
            }

            logger.info("Returning response to requesting client")
        }
        return yieldResponse
    }
}
