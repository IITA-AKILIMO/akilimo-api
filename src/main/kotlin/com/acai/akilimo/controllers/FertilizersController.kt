package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.FertilizerDto
import com.acai.akilimo.request.FertilizerRequest
import com.acai.akilimo.service.FertilizerService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid

@RequestMapping("/api/v2/fertilizers")
@RestController
class FertilizersController(private val fertilizerService: FertilizerService
) : BaseController() {
    companion object {
        val logger = LoggerFactory.getLogger(FertilizersController::class.java)
    }

    @GetMapping
    fun listFertilizers(@RequestHeader("country-code") countryCode: String): ResponseEntity<List<FertilizerDto>> {
        val fertilizerList = fertilizerService.fertilizers(countryCode)

        return ResponseEntity(fertilizerList, HttpStatus.OK)
    }


    @PostMapping
    fun addFertilizer(@Valid @RequestBody fertilizerRequest: FertilizerRequest): ResponseEntity<FertilizerDto> {

        val fertilizerPriceDto = fertilizerService.saveFertilizer(fertilizerRequest)

        return ResponseEntity(fertilizerPriceDto, HttpStatus.OK)
    }
}