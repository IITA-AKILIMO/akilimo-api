package com.iita.akilimo.api.controllers

import com.fasterxml.jackson.databind.ObjectMapper
import com.iita.akilimo.core.mapper.RequestStatsDto
import com.iita.akilimo.core.service.RequestStatsService
import io.swagger.v3.oas.annotations.Operation
import io.swagger.v3.oas.annotations.Parameter
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.http.HttpHeaders
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
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

    @GetMapping("/requests/json")
    @Operation(summary = "Download recommendations request as a json file", description = "", tags = ["Requests"])
    fun downloadRequestStats(): ResponseEntity<ByteArray?>? {
        val userFeedbackList = statsService.getRequestStats()
        val objectMapper = ObjectMapper()
        val json = objectMapper.writeValueAsString(userFeedbackList)
        val isr = json.toByteArray()
        val fileName = "request_stats.json"
        val respHeaders = HttpHeaders()
        respHeaders.contentLength = isr.size.toLong()
        respHeaders.contentType = MediaType("text", "json")
        respHeaders.cacheControl = "must-revalidate, post-check=0, pre-check=0"
        respHeaders.set(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=$fileName")

        return ResponseEntity(isr, respHeaders, HttpStatus.OK)
    }
}
