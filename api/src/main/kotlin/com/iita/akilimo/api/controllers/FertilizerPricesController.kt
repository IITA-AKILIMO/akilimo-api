package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.FertilizerPriceDto
import com.iita.akilimo.core.request.FertilizerPriceRequest
import com.iita.akilimo.core.service.FertilizerPriceService
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v2/fertilizer-prices")
@RestController
class FertilizerPricesController(private val fertilizerPriceService: FertilizerPriceService) : BaseController() {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(this::class.java)
    }

    @GetMapping
    fun listPrices(@RequestHeader("country-code") countryCode: String): ResponseEntity<List<FertilizerPriceDto>> {
        val fertilizerList = fertilizerPriceService.fertilizerPriceByCountry(countryCode)

        return ResponseEntity(fertilizerList, HttpStatus.OK)
    }

    @GetMapping("/{fertilizerId}")
    fun fertilizerPriceById(@PathVariable fertilizerId: Long): ResponseEntity<List<FertilizerPriceDto>> {

        val fertilizerPriceDto = fertilizerPriceService.fertilizerPrices(fertilizerId = fertilizerId)

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


    @PostMapping
    fun addFertilizerPrice(@Valid @RequestBody fertilizerPriceRequest: FertilizerPriceRequest): ResponseEntity<FertilizerPriceDto> {

        val fertilizerPriceDto = fertilizerPriceService.saveFertilizerPrice(fertilizerPriceRequest)

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
