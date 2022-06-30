package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.core.service.MaizePriceService
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
@RequestMapping("/api/v1/maize-prices")
@RestController
class MaizePricesController(private val maizePriceService: MaizePriceService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(MaizePricesController::class.java)
    }

    @GetMapping()
    fun getAllPrices(): ResponseEntity<List<ProducePriceDto>> {
        val priceDto = maizePriceService.fetchAllPrices()

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/inactive")
    fun getInactivePrices(): ResponseEntity<List<ProducePriceDto>> {
        val priceDto = maizePriceService.fetchAllInactivePrices()

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/{id}")
    fun getPriceById(@PathVariable id: Long): ResponseEntity<ProducePriceDto> {
        val priceDto = maizePriceService.findMaizePricePriceById(id)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/country/{country}")
    fun listPrices(@PathVariable country: EnumCountry): ResponseEntity<List<ProducePriceDto>> {
        val priceList = maizePriceService.maizePrices(country)

        return ResponseEntity(priceList, HttpStatus.OK)
    }

    @GetMapping("/country/{country}/produce/{produceType}")
    fun listPrices(@PathVariable country: EnumCountry, @PathVariable produceType: String): ResponseEntity<List<ProducePriceDto>> {
        val priceList = maizePriceService.maizePrices(country, produceType)

        return ResponseEntity(priceList, HttpStatus.OK)
    }

    @PostMapping(produces = [MediaType.APPLICATION_JSON_VALUE])
    fun addMaizePrice(@Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest): ResponseEntity<ProducePriceDto> {

        val priceDto = maizePriceService.saveMaizePrice(fertilizerPriceRequest)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @PutMapping("/{id}")
    fun updateMaizePrice(
        @PathVariable id: Long,
        @Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest
    ): ResponseEntity<ProducePriceDto> {

        val priceDto = maizePriceService.updateMaizePrice(id, fertilizerPriceRequest)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @DeleteMapping("/{id}")
    fun deleteMaizePrice(@PathVariable id: Long): ResponseEntity<Boolean> {

        logger.info("The id being passed here is by the name $id")
        val recordDeleted = maizePriceService.deleteMaizePrice(id)

        return when {
            recordDeleted -> ResponseEntity(recordDeleted, HttpStatus.OK)
            else -> ResponseEntity(recordDeleted, HttpStatus.NOT_FOUND)
        }
    }
}
