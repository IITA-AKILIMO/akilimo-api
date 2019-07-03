package com.acai.akilimo.controllers

import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController

@RequestMapping("/api/v2/fertilizer-prices")
@RestController
class FertilizerPricesController {

    private val logger = LoggerFactory.getLogger(FertilizerPricesController::class.java)

    @GetMapping
    fun listPrices() {
    }
}