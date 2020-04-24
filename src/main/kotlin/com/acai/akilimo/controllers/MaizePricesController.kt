package com.acai.akilimo.controllers

import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.ProducePriceDto
import com.acai.akilimo.request.ProducePriceRequest
import com.acai.akilimo.service.MaizePriceService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v4/maize-prices")
@RestController
class MaizePricesController(private val maizePriceService: MaizePriceService) {

    companion object {
        val logger = LoggerFactory.getLogger(MaizePricesController::class.java)
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
        val priceDto = maizePriceService.findCassavaPriceById(id)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @GetMapping("/country/{country}")
    fun listPrices(@PathVariable country: EnumCountry): ResponseEntity<List<ProducePriceDto>> {
        val priceList = maizePriceService.cassavaPrices(country)

        return ResponseEntity(priceList, HttpStatus.OK)
    }

    @PostMapping(produces = [MediaType.APPLICATION_JSON_VALUE])
    fun addFertilizerPrice(@Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest): ResponseEntity<ProducePriceDto> {

        val priceDto = maizePriceService.saveFertilizerPrice(fertilizerPriceRequest)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @PutMapping("/{id}")
    fun updateFertilizerPrice(
            @PathVariable id: Long,
            @Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest
    ): ResponseEntity<ProducePriceDto> {

        val priceDto = maizePriceService.updateCassavaPrice(id, fertilizerPriceRequest)

        return ResponseEntity(priceDto, HttpStatus.OK)
    }

    @DeleteMapping("/{id}")
    fun deleteFertilizerPrice(@PathVariable id: Long): ResponseEntity<Boolean> {

        logger.info("The id being passed here is by the name $id")
        val recordDeleted = maizePriceService.deleteCassavaPrice(id)

        return when {
            recordDeleted -> ResponseEntity(recordDeleted, HttpStatus.OK)
            else -> ResponseEntity(recordDeleted, HttpStatus.NOT_FOUND)
        }
    }
}