package com.iita.akilimo.api.controllers.rec


import com.iita.akilimo.api.controllers.BaseController
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.usecases.fr.BasicFrRequest
import com.iita.akilimo.core.service.basicrec.BasicRecService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


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
        @RequestParam(required = true) @Valid country: String,
        @RequestParam(required = true) area: Double,
        @RequestParam(required = true, defaultValue = "11") fcy: Int,
        @RequestParam(required = true) plantingMonth: Int,
        @RequestParam(required = true) lat: Double,
        @RequestParam(required = true) lon: Double,
        @RequestParam(required = true) maxInv: Double,
        @RequestParam(required = true) cassUp: Double,
        @RequestParam(required = true) ureaAvailable: Boolean,
        @RequestParam(required = true) ureaPrice: Double,
        @RequestParam(required = true) npk15Available: Boolean,
        @RequestParam(required = true) npk15Price: Double,
        @RequestParam(required = true) npk17Available: Boolean,
        @RequestParam(required = true) npk17Price: Double,
    ): ResponseEntity<RecommendationResponseDto> {

        val basicFrRequest = BasicFrRequest(
            country = country,
            currentFieldYield = fcy,
            lat = lat,
            lon = lon,
            area = area,
             maxInv =maxInv,
            cassUp =cassUp,
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
