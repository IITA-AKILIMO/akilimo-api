package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.RequestStatsDto
import com.iita.akilimo.core.service.RequestStatsService
import io.swagger.v3.oas.annotations.Operation
import io.swagger.v3.oas.annotations.Parameter
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController


@RequestMapping("/api/v1/stats")
@RestController
class RequestStatsController(private val statsService: RequestStatsService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(RequestStatsController::class.java)
    }


    @GetMapping("/requests")
    @Operation(summary = "List recommendations request", description = "", tags = ["Requests"])
    fun listRecommendationRequests(@Parameter(hidden = true) pageable: Pageable): ResponseEntity<Page<RequestStatsDto>> {

        val userFeedback: Page<RequestStatsDto> = statsService.getRequestStats(pageable)

        return ResponseEntity(userFeedback, HttpStatus.OK)
    }
}
