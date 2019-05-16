package com.acai.akilimo.controllers


import com.acai.akilimo.config.ConfigProperties
import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.entities.RecommendationResponse
import com.acai.akilimo.generators.HtmlFileToPdfGenerator
import com.acai.akilimo.mapper.RecommendationRequestDto
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.Plumber
import com.acai.akilimo.service.RecommendationServiceImp
import org.modelmapper.ModelMapper
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
constructor(private val recommendationServiceImp: RecommendationServiceImp, private val restTemplate: RestTemplate, configProperties: ConfigProperties) {

    private val logger = LoggerFactory.getLogger(RecommendationsController::class.java)

    private val plumberProperties: Plumber = configProperties.plumber()

    @GetMapping
    fun listYieldRequests(): List<RecommendationRequest> {
        return recommendationServiceImp.findAll()
    }

    @PostMapping("/compute")
    fun processYieldRequest(@RequestBody recommendationRequest: RecommendationRequestDto): RecommendationResponseDto? {
        val modelMapper = ModelMapper()


        val g = HtmlFileToPdfGenerator()

        g.readHtmlFileTest()
        //val j = g.readHtmlFile()

        return null;
        /*
        val request = modelMapper.map(recommendationRequest, RecommendationRequest::class.java)

        val response = recommendationServiceImp.saveRecommendationRequest(request!!)
        if (response != null) {
            return modelMapper.map(response, RecommendationResponseDto::class.java)
        }

        return null*/
    }

    @Deprecated("Will be removed")
    @PostMapping("/compute/direct")
    fun processDirectYieldRequest(@RequestBody recommendationRequestDto: RecommendationRequestDto): RecommendationResponseDto {
        val recommendationResponseDto = RecommendationResponseDto()

        val headers = HttpHeaders()
        headers.add("Content-Type", MediaType.APPLICATION_JSON.toString())

        logger.info("Payload is $recommendationRequestDto")
        logger.info("Request has entered here, proceeding " + recommendationRequestDto.harvestDate!!)
        //send to plumber
        val entity = HttpEntity(recommendationRequestDto, headers)
        val theUrl = plumberProperties.endpoint!! + "/estimate/compute"

        logger.info("Going to endpoint $theUrl")
        val response = restTemplate.postForEntity(
                theUrl, entity, Array<RecommendationResponse>::class.java)

        val objects = response.body

        if (objects != null) {
            recommendationResponseDto.recommendationText = objects[2].toString()
        }

        logger.info("Returning response to requesting client")

        return recommendationResponseDto
    }
}
