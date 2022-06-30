package com.iita.akilimo.api.controllers.rec


import com.iita.akilimo.api.controllers.BaseController
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.usecases.fr.BasicFrRequest
import com.iita.akilimo.core.service.basicrec.BasicRecService
import io.swagger.v3.oas.annotations.media.Schema
import io.swagger.v3.oas.annotations.tags.Tag
import io.swagger.v3.oas.annotations.tags.Tags
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@Tags(Tag(name = "basic", description = "Basic API"))
@RequestMapping("/api/v2/recommendations/basic")
@RestController
class BasicCasesControllers(
    private val basicRecService: BasicRecService
) : BaseController() {

    companion object {
        private val myLogger = LoggerFactory.getLogger(this::class.java)
    }


    @PostMapping("/fr")
    fun computeFrRecPost(
        @Valid @RequestBody basicFrRequest: BasicFrRequest
    ): ResponseEntity<RecommendationResponseDto> {

        myLogger.info("Processing FR request for Basic API")
        val resp = basicRecService.computeFrRecommendation(basicFrRequest)
        return ResponseEntity(resp, HttpStatus.OK)
    }

    @GetMapping("/fr")
    fun computeFrRecGet(
        @RequestParam(required = true) @Schema(example = "ng") @Valid country: String,
        @RequestParam(required = true) area: Double,
        @RequestParam(required = true) @Schema(example = "11") fcy: Int,
        @RequestParam(required = true) @Schema(example = "10") plantingMonth: Int,
        @RequestParam(required = true) @Schema(example = "7.4201") lat: Double,
        @RequestParam(required = true) @Schema(example = "5.1063") lon: Double,
        @RequestParam(required = true) @Schema(example = "150000") maxInv: Double,
        @RequestParam(required = true) @Schema(example = "50000") cassUp: Double,
        @RequestParam(required = true) @Schema(example = "true") ureaAvailable: Boolean,
        @RequestParam(required = true) @Schema(example = "0") ureaPrice: Double,
        @RequestParam(required = true) @Schema(example = "true") npk15Available: Boolean,
        @RequestParam(required = true) @Schema(example = "0") npk15Price: Double,
        @RequestParam(required = true) @Schema(example = "true") npk17Available: Boolean,
        @RequestParam(required = true) @Schema(example = "0") npk17Price: Double,
    ): ResponseEntity<RecommendationResponseDto> {

        val basicFrRequest = BasicFrRequest(
            country = country.uppercase(),
            currentFieldYield = fcy,
            lat = lat,
            lon = lon,
            area = area,
            maxInv = maxInv,
            cassUp = cassUp,
            areaUnit = "ha",
            plantingMonth = plantingMonth,
            ureaAvailable = ureaAvailable,
            ureaPrice = ureaPrice,
            npk15Available = npk15Available,
            npk15Price = npk15Price,
            npk17Available = npk17Available,
            npk17Price = npk17Price
        )
        myLogger.info("Processing FR request for Basic API")
        val resp = basicRecService.computeFrRecommendation(basicFrRequest)
        return ResponseEntity(resp, HttpStatus.OK)
    }
}
