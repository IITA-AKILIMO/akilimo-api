package com.acai.akilimo.controllers


import com.acai.akilimo.entities.Recommendation
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.request.RecommendationRequest
import com.acai.akilimo.service.MessagingService
import com.acai.akilimo.service.RecommendationService
import com.fasterxml.jackson.databind.ObjectMapper
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import org.springframework.http.HttpStatus
import javax.validation.Valid


@RequestMapping("/api/v2/recommendations")
@RestController
class FertilizerRecommendationsController
@Autowired
constructor(private val recommendationService: RecommendationService, private val messagingService: MessagingService) {

    private val logger = LoggerFactory.getLogger(FertilizerRecommendationsController::class.java)

    @PostMapping("/fertilizer-test")
    fun requestTest(@Valid @RequestBody recommendationRequest: RecommendationRequest): ResponseEntity<RecommendationResponseDto> {
        val modelMapper = ModelMapper()
        val mapper = ObjectMapper()
        var recommendationResponseDto: RecommendationResponseDto? = null

        logger.info("Request from mobile application is");
        logger.info(mapper.writerWithDefaultPrettyPrinter().writeValueAsString(recommendationRequest))
        //val response = recommendationService.saveRecommendationRequest(request!!)
        val fertilizerList = recommendationService.prepareFertilizerList(recommendationRequest.fertilizerList)
        val response = recommendationService.computeRecommendations(recommendationRequest.computeRequest, fertilizerList)


        return when {
            response != null -> {
                //send sms
                when {
                    recommendationRequest.computeRequest.sendSms && response.hasResponse -> {
                        val resp = messagingService.sendTextMessage(response)
                        logger.debug("Sms sending response ${resp.toString()}")
                    }
                    else -> logger.info("There are no responses for sending the sms")
                }

                when {
                    recommendationRequest.computeRequest.email && response.hasResponse -> {
                        val resp = messagingService.sendEmailMessage(response)
                        logger.info("email sending response $resp")
                    }
                }
                recommendationResponseDto = modelMapper.map(response, RecommendationResponseDto::class.java)
                ResponseEntity(recommendationResponseDto, HttpStatus.OK)
            }
            else -> ResponseEntity(recommendationResponseDto, HttpStatus.EXPECTATION_FAILED)
        }

    }
}
