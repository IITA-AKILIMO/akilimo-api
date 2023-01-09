package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.FertilizerDto
import com.iita.akilimo.core.request.FertilizerRequest
import com.iita.akilimo.core.service.FertilizerService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid

@RequestMapping("/api/v1/fertilizers")
@RestController
class FertilizersController(
    private val fertilizerService: FertilizerService
) : BaseController() {
    companion object {
        val logger = LoggerFactory.getLogger(FertilizersController::class.java)
    }


    @GetMapping
    fun listFertilizers(@RequestHeader httpHeaders: Map<String, String>): ResponseEntity<List<FertilizerDto>> {

        var useCase: String? = null;
        val countryCode: String = httpHeaders.getValue("country-code")
        if (httpHeaders.containsKey("use-case")) {
            useCase = httpHeaders.getValue("use-case")
        }

        val fertilizerList = fertilizerService.fertilizers(countryCode, useCase)


        return ResponseEntity(fertilizerList, HttpStatus.OK)
    }

    @PostMapping
    fun addFertilizer(@Valid @RequestBody fertilizerRequest: FertilizerRequest): ResponseEntity<FertilizerDto> {

        val fertilizerPriceDto = fertilizerService.saveFertilizer(fertilizerRequest)

        return ResponseEntity(fertilizerPriceDto, HttpStatus.OK)
    }
}
