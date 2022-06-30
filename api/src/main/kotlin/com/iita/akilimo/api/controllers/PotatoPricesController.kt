package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.core.service.PotatoPriceService
import com.iita.akilimo.enums.EnumCountry
import io.swagger.v3.oas.annotations.Hidden
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid

@Hidden
@RequestMapping("/api/v1/potato-prices")
@RestController
class PotatoPricesController(private val potatoPriceService: PotatoPriceService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(PotatoPricesController::class.java)
    }

    @GetMapping()
    fun getAllPrices(): ResponseEntity<List<ProducePriceDto>> {
        val priceDto = potatoPriceService.fetchAllPrices()

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/inactive")
    fun getInactivePrices(): ResponseEntity<List<ProducePriceDto>> {
        val priceDto = potatoPriceService.fetchAllInactivePrices()

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/{id}")
    fun getPriceById(@PathVariable id: Long): ResponseEntity<ProducePriceDto> {
        val priceDto = potatoPriceService.findPotatoPriceById(id)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/country/{country}")
    fun listPrices(@PathVariable country: EnumCountry): ResponseEntity<List<ProducePriceDto>> {
        val priceList = potatoPriceService.potatoPrices(country)

        return ResponseEntity(priceList, HttpStatus.OK)
    }

    @PostMapping(produces = [MediaType.APPLICATION_JSON_VALUE])
    fun addPotatoPrice(@Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest): ResponseEntity<ProducePriceDto> {

        val priceDto = potatoPriceService.savePotatoPrice(fertilizerPriceRequest)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @PutMapping("/{id}")
    fun updatePotatoPrice(
            @PathVariable id: Long,
            @Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest
    ): ResponseEntity<ProducePriceDto> {

        val priceDto = potatoPriceService.updatePotatoPrice(id, fertilizerPriceRequest)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @DeleteMapping("/{id}")
    fun deletePotatoPrice(@PathVariable id: Long): ResponseEntity<Boolean> {

        logger.info("The id being passed here is by the name $id")
        val recordDeleted = potatoPriceService.deletePotatoPrice(id)

        return when {
            recordDeleted -> ResponseEntity(recordDeleted, HttpStatus.OK)
            else -> ResponseEntity(recordDeleted, HttpStatus.NOT_FOUND)
        }
    }
}
