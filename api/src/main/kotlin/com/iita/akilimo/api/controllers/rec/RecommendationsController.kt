package com.iita.akilimo.api.controllers.rec


import com.iita.akilimo.api.controllers.BaseController
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.RecommendationRequest
import com.iita.akilimo.core.service.MessagingService
import com.iita.akilimo.core.service.RecommendationService
import io.swagger.v3.oas.annotations.Operation
import io.swagger.v3.oas.annotations.tags.Tag
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid

@RequestMapping("/api/v2/recommendations")
@Tag(name = "AKILIMO")
@RestController
class RecommendationsController(
    private val recommendationService: RecommendationService,
    private val messagingService: MessagingService
) : BaseController() {

    companion object {
        private val myLogger = LoggerFactory.getLogger(RecommendationsController::class.java)
    }

    @PostMapping
    @Operation(deprecated = true)
    fun computeRecommendations(
        @Valid @RequestBody recommendationRequest: RecommendationRequest,
        @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]

        //check that the request has defined fertilizers
        val modelMapper = ModelMapper()

        val response = recommendationService.computeRecommendations(recommendationRequest)

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
