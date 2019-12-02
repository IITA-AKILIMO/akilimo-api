package com.acai.akilimo.controllers


import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.request.RecommendationRequest
import com.acai.akilimo.service.MessagingService
import com.acai.akilimo.service.RecommendationService
import com.fasterxml.jackson.databind.ObjectMapper
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.PostMapping
import org.springframework.web.bind.annotation.RequestBody
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController
import javax.validation.Valid


@RequestMapping("/api/v2/recommendations")
@RestController
class RecommendationsController(private val recommendationService: RecommendationService, private val messagingService: MessagingService) : BaseController() {

    companion object {
        private val myLogger = LoggerFactory.getLogger(RecommendationsController::class.java)
    }

    @PostMapping
    fun computeRecommendations(@Valid @RequestBody recommendationRequest: RecommendationRequest): ResponseEntity<RecommendationResponseDto> {
        val modelMapper = ModelMapper()
        val mapper = ObjectMapper()
        var recommendationResponseDto: RecommendationResponseDto? = null
        val mobileData = mapper.writerWithDefaultPrettyPrinter().writeValueAsString(recommendationRequest)
        myLogger.info("Request from mobile application is\n");
        myLogger.info(mobileData)

        val response = recommendationService.computeRecommendations(recommendationRequest)

        when {
            response != null -> {
                if (response.hasResponse) {
                    messagingService.sendEmailMessage(response, recommendationRequest.userInfo.sendEmail)
                    messagingService.sendTextMessage(response, recommendationRequest.userInfo.sendSms)
                }
                recommendationResponseDto = modelMapper.map(response, RecommendationResponseDto::class.java)
                return ResponseEntity(recommendationResponseDto, HttpStatus.OK)
            }
        }
        return ResponseEntity(recommendationResponseDto, HttpStatus.EXPECTATION_FAILED)
    }
}