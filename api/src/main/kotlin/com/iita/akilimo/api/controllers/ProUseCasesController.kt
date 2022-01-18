package com.iita.akilimo.api.controllers


import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.usecases.fr.FrRequest
import com.iita.akilimo.core.request.usecases.ic.IcRequest
import com.iita.akilimo.core.request.usecases.sp.SpRequest
import com.iita.akilimo.core.service.MessagingService
import com.iita.akilimo.core.service.RecommendationService
import io.swagger.v3.oas.annotations.tags.Tag
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v1/recommendations/pro")
@RestController
@Tag(name = "AKILIMO recommendations", description = "Operations pertaining recommendations for various use cases")
class UseCasesControllers(
    private val recService: RecommendationService,
    private val messagingService: MessagingService
) : BaseController() {

    companion object {
        private val myLogger = LoggerFactory.getLogger(this::class.java)
    }

    @PostMapping("/fr")
    fun computeFrRec(
        @Valid @RequestBody frRequest: FrRequest,
        @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]
        myLogger.info("Processing FR request for Pro API for context $requestContext")
        val modelMapper = ModelMapper()
        val response = recService.computeFrRecommendation(frRequest)
        when {
            response != null -> {
                if (response.hasResponse) {
                    messagingService.sendEmailMessage(response, frRequest.userInfo.sendEmail)
                    messagingService.sendTextMessage(response, frRequest.userInfo.sendSms)
                }
                val resp = modelMapper.map(response, RecommendationResponseDto::class.java)
                return ResponseEntity(resp, HttpStatus.OK)
            }
        }
        return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
    }

    @PostMapping("/ic")
    fun computeIcRec(
        @Valid @RequestBody icRequest: IcRequest,
        @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]
        myLogger.info("Processing IC request for Pro API for context $requestContext")
        val modelMapper = ModelMapper()
        val response = recService.computeIcRecommendation(icRequest)
        when {
            response != null -> {
                if (response.hasResponse) {
                    messagingService.sendEmailMessage(response, icRequest.userInfo.sendEmail)
                    messagingService.sendTextMessage(response, icRequest.userInfo.sendSms)
                }
                val resp = modelMapper.map(response, RecommendationResponseDto::class.java)
                return ResponseEntity(resp, HttpStatus.OK)
            }
        }
        return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
    }

    @PostMapping("/sp")
    fun computeSpRec(
        @Valid @RequestBody spRequest: SpRequest,
        @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]
        myLogger.info("Processing SP request for Pro API for context $requestContext")
        //check that the request has defined fertilizers
        val modelMapper = ModelMapper()
        val response = recService.computeSpRecommendation(spRequest)
        when {
            response != null -> {
                if (response.hasResponse) {
                    messagingService.sendEmailMessage(response, spRequest.userInfo.sendEmail)
                    messagingService.sendTextMessage(response, spRequest.userInfo.sendSms)
                }
                val resp = modelMapper.map(response, RecommendationResponseDto::class.java)
                return ResponseEntity(resp, HttpStatus.OK)
            }
        }
        return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
    }
}
