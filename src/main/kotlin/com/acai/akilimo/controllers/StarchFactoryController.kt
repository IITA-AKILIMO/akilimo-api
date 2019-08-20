package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.StarchFactoryDto
import com.acai.akilimo.request.StarchFactoryRequest
import com.acai.akilimo.service.StarchFactoryService
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid

@RequestMapping("/api/v2/starch-factories")
@RestController
class StarchFactoryController(private val starchFactoryService: StarchFactoryService) : BaseController() {
    companion object {
        val logger: Logger = LoggerFactory.getLogger(StarchFactoryController::class.java)
    }

    @GetMapping
    fun listFactories(@RequestHeader("country-code") countryCode: String): ResponseEntity<List<StarchFactoryDto>> {
        val fertilizerList = starchFactoryService.factories(countryCode)

        return ResponseEntity(fertilizerList, HttpStatus.OK)
    }

    @PostMapping
    fun addFactory(@Valid @RequestBody starchFactoryRequest: StarchFactoryRequest): ResponseEntity<StarchFactoryDto> {

        val fertilizerPriceDto = starchFactoryService.saveFactory(starchFactoryRequest)

        return ResponseEntity(fertilizerPriceDto, HttpStatus.OK)
    }
}