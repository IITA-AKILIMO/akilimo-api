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


    @GetMapping("/{fertilizerKey}")
    fun fertilizerPriceByKey(
        @RequestHeader("country-code") countryCode: String,
        @PathVariable fertilizerKey: String
    ): ResponseEntity<List<FertilizerPriceDto>> {

        val fertilizerPriceDto = fertilizerPriceService.fertilizerPrices(
            fertilizerKey = fertilizerKey.uppercase(),
            countryCode = countryCode
        )

        return ResponseEntity(fertilizerPriceDto, HttpStatus.OK)
    }
}
