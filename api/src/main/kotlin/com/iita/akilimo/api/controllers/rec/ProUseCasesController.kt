package com.iita.akilimo.api.controllers.rec


import com.iita.akilimo.api.controllers.BaseController
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.usecases.bpp.BppRequest
import com.iita.akilimo.core.request.usecases.fr.FrRequest
import com.iita.akilimo.core.request.usecases.ic.IcRequest
import com.iita.akilimo.core.service.MessagingService
import com.iita.akilimo.core.service.RecommendationService
import io.swagger.v3.oas.annotations.Hidden
import io.swagger.v3.oas.annotations.tags.Tag
import io.swagger.v3.oas.annotations.tags.Tags
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@Tags(Tag(name = "advanced", description = "Advanced API"))
@RequestMapping("/api/v2/recommendations/pro")
@RestController
class UseCasesControllers(
    private val recService: RecommendationService, private val messagingService: MessagingService
) : BaseController() {

    private val myLogger = LoggerFactory.getLogger(this::class.java)


    @PostMapping("/fr")
    fun computeFrRec(
        @Valid @RequestBody frRequest: FrRequest, @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
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

    @Hidden
    @PostMapping("/ic/crop/{crop}")
    fun computeIcRec(
        @PathVariable(required = true) crop: String, @Valid @RequestBody icRequest: IcRequest, @RequestHeader headers: Map<String, String>
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

    @Hidden
    @PostMapping("/sp")
    fun computeSpRec(
        @Valid @RequestBody bppRequest: BppRequest, @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]
        myLogger.info("Processing SP request for Pro API for context $requestContext")
        //check that the request has defined fertilizers
        val modelMapper = ModelMapper()
        val response = recService.computeSpRecommendation(bppRequest)
        when {
            response != null -> {
                if (response.hasResponse) {
                    messagingService.sendEmailMessage(response, bppRequest.userInfo.sendEmail)
                    messagingService.sendTextMessage(response, bppRequest.userInfo.sendSms)
                }
                val resp = modelMapper.map(response, RecommendationResponseDto::class.java)
                return ResponseEntity(resp, HttpStatus.OK)
            }
        }
        return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
    }

    @Hidden
    @PostMapping("/bpp")
    fun computeBppRec(
        @Valid @RequestBody bppRequest: BppRequest, @RequestHeader headers: Map<String, String>
    ): ResponseEntity<RecommendationResponseDto> {

        val requestContext = headers["context"]
        val localeLanguage = headers["locale-lang"]
        myLogger.info("Processing BPP request for Pro API for context $requestContext")
        //check that the request has defined fertilizers
        val modelMapper = ModelMapper()
        val response = recService.computeBppRecommendation(request = bppRequest)
        when {
            response != null -> {
                if (response.hasResponse) {
                    messagingService.sendEmailMessage(response, bppRequest.userInfo.sendEmail)
                    messagingService.sendTextMessage(response, bppRequest.userInfo.sendSms)
                }
                val resp = modelMapper.map(response, RecommendationResponseDto::class.java)
                return ResponseEntity(resp, HttpStatus.OK)
            }
        }
        return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
    }
}
