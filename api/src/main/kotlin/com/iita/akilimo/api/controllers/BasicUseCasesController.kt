package com.iita.akilimo.api.controllers


import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.usecases.fr.FrRequest
import com.iita.akilimo.core.request.usecases.ic.IcRequest
import com.iita.akilimo.core.request.usecases.bpp.BppRequest
import com.iita.akilimo.core.service.MessagingService
import com.iita.akilimo.core.service.RecommendationService
import io.swagger.v3.oas.annotations.tags.Tag
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v1/recommendations/basic")
@RestController
@Tag(name = "AKILIMO recommendations for basic applications", description = "Operations pertaining recommendations for various interventions")
class BasicCasesControllers(
    private val recService: RecommendationService, private val messagingService: MessagingService
) : BaseController() {

    companion object {
        private val myLogger = LoggerFactory.getLogger(this::class.java)
    }


    @RequestMapping("/fr", method = [RequestMethod.GET, RequestMethod.POST])
    fun computeFrRec(
        @RequestParam(defaultValue = "ha", required = true) areaUnit: String,
        @RequestParam(required = false) fieldSize: Double,
        @RequestParam(required = true) fcy: Int,
        @RequestParam(required = true) plantingMonth: Int,
        @RequestParam(required = true) state: String,
        @RequestParam(required = false) language: String,
        @RequestParam(required = false) output: String,
    ): ResponseEntity<RecommendationResponseDto> {

        myLogger.info("Processing FR request for Basic API")
        return ResponseEntity(RecommendationResponseDto(), HttpStatus.BAD_REQUEST)
    }
}
