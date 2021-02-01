package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.CurrencyDto
import com.acai.akilimo.service.CurrencyService
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController


@RequestMapping("/api/v3/currency")
@RestController
class CurrencyController(private val cassavaPriceService: CurrencyService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(CurrencyController::class.java)
    }

    @GetMapping()
    fun getAllCurrencies(): ResponseEntity<List<CurrencyDto>> {
        val currencyList = cassavaPriceService.fetchAllCurrencies()

        return ResponseEntity(currencyList, HttpStatus.OK)
    }
}