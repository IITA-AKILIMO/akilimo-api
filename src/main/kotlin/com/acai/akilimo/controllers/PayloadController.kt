package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.PayloadDto
import com.acai.akilimo.service.PayloadService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*


@RequestMapping("/api/v3/payload")
@RestController
class PayloadController(private val payloadService: PayloadService) : BaseController() {

    companion object {
        val logger = LoggerFactory.getLogger(PayloadController::class.java)
    }

    @GetMapping("/{requestId}")
    fun lisPayloadById(@PathVariable requestId: Long): ResponseEntity<PayloadDto> {
        val operationCostList = payloadService.findPayloadByRequestId(requestId)
        return ResponseEntity(operationCostList, HttpStatus.OK)
    }

    @GetMapping
    fun listPayloads(): ResponseEntity<List<PayloadDto>> {

        val operationCostList = payloadService.payloadList();

        return ResponseEntity(operationCostList, HttpStatus.OK)
    }
}