package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.FertilizerPriceDto
import com.iita.akilimo.core.mapper.InvestmentAmountDto
import com.iita.akilimo.core.service.FertilizerPriceService
import com.iita.akilimo.core.service.InvestmentAmountService
import io.swagger.v3.oas.annotations.Hidden
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*

@Hidden
@RequestMapping("/api/v1/investment-amount")
@RestController
class InvestmentAmountController(private val investmentService: InvestmentAmountService) : BaseController() {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(this::class.java)
    }


    @GetMapping("/{countryCode}/country")
    fun investmentByCountryCode(
        @PathVariable countryCode: String
    ): ResponseEntity<List<InvestmentAmountDto>> {

        val investmentDto = investmentService.investmentAmountByCountry(countryCode)

        return ResponseEntity(investmentDto, HttpStatus.OK)
    }
}
