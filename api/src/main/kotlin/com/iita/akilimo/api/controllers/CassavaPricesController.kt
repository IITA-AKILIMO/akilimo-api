package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.core.service.CassavaPriceService
import com.iita.akilimo.enums.EnumCountry
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping(
    value = [
        "/api/v1/cassava-prices",
        "/api/v3/cassava-prices"
    ]
)
@RestController
class CassavaPricesController(val cassavaPriceService: CassavaPriceService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(CassavaPricesController::class.java)
    }

    @GetMapping()
    fun getAllPrices(): ResponseEntity<List<ProducePriceDto>> {
        val cassavaPriceDto = cassavaPriceService.fetchAllPrices()

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @GetMapping("/inactive")
    fun getInactivePrices(): ResponseEntity<List<ProducePriceDto>> {
        val cassavaPriceDto = cassavaPriceService.fetchAllInactivePrices()

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @GetMapping("/{id}")
    fun getPriceById(@PathVariable id: Long): ResponseEntity<ProducePriceDto> {
        val cassavaPriceDto = cassavaPriceService.findCassavaPriceById(id)

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @GetMapping("/country/{country}")
    fun listPrices(@PathVariable country: EnumCountry): ResponseEntity<List<ProducePriceDto>> {
        val cassavaPriceList = cassavaPriceService.cassavaPrices(country)

        return ResponseEntity(cassavaPriceList, HttpStatus.OK)
    }

    @PostMapping(produces = [MediaType.APPLICATION_JSON_VALUE])
    fun addCassava(@Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest): ResponseEntity<ProducePriceDto> {

        val cassavaPriceDto = cassavaPriceService.saveFertilizerPrice(fertilizerPriceRequest)

        return ResponseEntity(cassavaPriceDto, HttpStatus.OK)
    }

    @PutMapping("/{id}")
    fun updateFertilizerPrice(
        @PathVariable id: Long,
        @Valid @RequestBody fertilizerPriceRequest: ProducePriceRequest
    ): ResponseEntity<ProducePriceDto> {

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
