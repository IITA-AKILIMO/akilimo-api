package com.acai.akilimo.controllers


import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.request.RecommendationRequest
import com.acai.akilimo.service.MessagingService
import com.acai.akilimo.service.RecommendationService
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.http.HttpHeaders
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.util.MultiValueMap
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v2/recommendations")
@RestController
class RecommendationsController(private val recommendationService: RecommendationService,
                                private val messagingService: MessagingService) : BaseController() {

    companion object {
        private val myLogger = LoggerFactory.getLogger(RecommendationsController::class.java)
    }

    @PostMapping
    fun computeRecommendations(@Valid @RequestBody recommendationRequest: RecommendationRequest,
                               @RequestHeader headers: Map<String, String>): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]

        //check that the request has defined fertilizers
        val modelMapper = ModelMapper()

        val fertilizers = recommendationRequest.fertilizerList

        if (fertilizers.isEmpty() || fertilizers.size < 2) {
            val resp = RecommendationResponseDto()
            myLogger.error("Empty fertilizer list here, tell user to retry again")
            return ResponseEntity(resp, HttpStatus.FAILED_DEPENDENCY)
        } else {
            val response = recommendationService.computeRecommendations(recommendationRequest, requestContext)

            when {
                response != null -> {
                    if (response.hasResponse) {
                        messagingService.sendEmailMessage(response, recommendationRequest.userInfo.sendEmail)
                        messagingService.sendTextMessage(response, recommendationRequest.userInfo.sendSms)
                    }
                    val resp = modelMapper.map(response, RecommendationResponseDto::class.java)
                    return ResponseEntity(resp, HttpStatus.OK)
                }
            }
            return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
        }
    }
}