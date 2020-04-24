package com.acai.akilimo.controllers

import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.CassavaPriceDto
import com.acai.akilimo.request.CassavaPriceRequest
import com.acai.akilimo.service.CassavaPriceService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v3/cassava-prices")
@RestController
class CassavaPricesController(private val cassavaPriceService: CassavaPriceService) {

    companion object {
        val logger = LoggerFactory.getLogger(CassavaPricesController::class.java)
    }

    @GetMapping()
    fun getAllPrices(): ResponseEntity<List<CassavaPriceDto>> {
        val cassavaPriceDto = cassavaPriceService.fetchAllPrices()

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @GetMapping("/inactive")
    fun getInactivePrices(): ResponseEntity<List<CassavaPriceDto>> {
        val cassavaPriceDto = cassavaPriceService.fetchAllInactivePrices()

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @GetMapping("/{id}")
    fun getPriceById(@PathVariable id: Long): ResponseEntity<CassavaPriceDto> {
        val cassavaPriceDto = cassavaPriceService.findCassavaPriceById(id)

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @GetMapping("/country/{country}")
    fun listPrices(@PathVariable country: EnumCountry): ResponseEntity<List<CassavaPriceDto>> {
        val cassavaPriceList = cassavaPriceService.cassavaPrices(country)

        return ResponseEntity(cassavaPriceList, HttpStatus.OK)
    }

    @PostMapping(produces = [MediaType.APPLICATION_JSON_VALUE])
    fun addFertilizerPrice(@Valid @RequestBody fertilizerPriceRequest: CassavaPriceRequest): ResponseEntity<CassavaPriceDto> {

        val cassavaPriceDto = cassavaPriceService.saveFertilizerPrice(fertilizerPriceRequest)

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @PutMapping("/{id}")
    fun updateFertilizerPrice(
            @PathVariable id: Long,
            @Valid @RequestBody fertilizerPriceRequest: CassavaPriceRequest
    ): ResponseEntity<CassavaPriceDto> {

        val cassavaPriceDto = cassavaPriceService.updateCassavaPrice(id, fertilizerPriceRequest)

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @DeleteMapping("/{id}")
    fun deleteFertilizerPrice(@PathVariable id: Long): ResponseEntity<Boolean> {

        logger.info("The id being passed here is by the name $id")
        val recordDeleted = cassavaPriceService.deleteCassavaPrice(id)

        return when {
            recordDeleted -> ResponseEntity(recordDeleted, HttpStatus.OK)
            else -> ResponseEntity(recordDeleted, HttpStatus.NOT_FOUND)
        }
    }
}