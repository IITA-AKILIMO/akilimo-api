package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.FertilizerPriceDto
import com.acai.akilimo.request.FertilizerPriceRequest
import com.acai.akilimo.service.FertilizerPriceService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v2/fertilizer-prices")
@RestController
class FertilizerPricesController(private val fertilizerPriceService: FertilizerPriceService) : BaseController() {

    companion object {
        val logger = LoggerFactory.getLogger(FertilizerPricesController::class.java)
    }

    @GetMapping
    fun listPrices(@RequestHeader("country-code") countryCode: String): ResponseEntity<List<FertilizerPriceDto>> {
        val fertilizerList = fertilizerPriceService.fertilizerPriceByCountry(countryCode)

        return ResponseEntity(fertilizerList, HttpStatus.OK)
    }

    @PostMapping
    fun addFertilizerPrice(@Valid @RequestBody fertilizerPriceRequest: FertilizerPriceRequest): ResponseEntity<FertilizerPriceDto> {

        val fertilizerPriceDto = fertilizerPriceService.saveFertilizerPrice(fertilizerPriceRequest)

        return ResponseEntity(fertilizerPriceDto, HttpStatus.OK)
    }

    @PutMapping("/{id}")
    fun updateFertilizerPrice(
            @PathVariable id: Long,
            @Valid @RequestBody fertilizerPriceRequest: FertilizerPriceRequest
    ): ResponseEntity<FertilizerPriceDto> {

        val fertilizerPriceDto = fertilizerPriceService.updateFertilizerPrice(id, fertilizerPriceRequest)

        return ResponseEntity(fertilizerPriceDto, HttpStatus.OK)
    }

    @DeleteMapping("/{id}/delete")
    fun deleteFertilizerPrice(@PathVariable id: Long): ResponseEntity<Boolean> {

        logger.info("The id being passed here is by the name $id")
        val recordDeleted = fertilizerPriceService.deleteFertilizerPrice(id)

        return when {
            recordDeleted -> ResponseEntity(recordDeleted, HttpStatus.OK)
            else -> ResponseEntity(recordDeleted, HttpStatus.NOT_FOUND)
        }
    }
}