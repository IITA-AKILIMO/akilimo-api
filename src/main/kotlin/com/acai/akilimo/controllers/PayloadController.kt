package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.PayloadDto
import com.acai.akilimo.service.PayloadService
import org.slf4j.LoggerFactory
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.PathVariable
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController


@RequestMapping("/api/v3/payload")
@RestController
class PayloadController(private val payloadService: PayloadService) : BaseController() {

    companion object {
        val logger = LoggerFactory.getLogger(PayloadController::class.java)
    }

    @GetMapping("/{id}")
    fun listPayloadById(@PathVariable id: Long): ResponseEntity<PayloadDto> {
        val payload = payloadService.findPayloadById(id)
        return ResponseEntity(payload, HttpStatus.OK)
    }

    @GetMapping("/{requestId}/request-id")
    fun listPayloadByRequestId(@PathVariable requestId: String): ResponseEntity<List<PayloadDto>> {
        val payloadList = payloadService.findPayloadByRequestId(requestId = requestId)
        return ResponseEntity(payloadList, HttpStatus.OK)
    }

    @GetMapping
    fun listAllPayloads(pageable: Pageable): ResponseEntity<Page<PayloadDto>> {

        val payloadList = payloadService.payloadList(pageable = pageable);

        return ResponseEntity(payloadList, HttpStatus.OK)
    }
}