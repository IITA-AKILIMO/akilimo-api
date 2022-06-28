package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.DateDto
import com.iita.akilimo.core.service.DateService
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController

@RequestMapping("/api/v1/dates")
@RestController
class DatesController(private val dateService: DateService) : BaseController() {
    companion object {
        val logger: Logger = LoggerFactory.getLogger(DatesController::class.java)
    }


    @GetMapping("/planting-dates")
    fun plantingDates(): ResponseEntity<List<DateDto>> {
        val dateList = dateService.plantingDates()

        return ResponseEntity(dateList, HttpStatus.OK)
    }

    @GetMapping("/harvest-dates")
    fun harvestDates(): ResponseEntity<List<DateDto>> {
        val dateList = dateService.harvestDates()

        return ResponseEntity(dateList, HttpStatus.OK)
    }
}
