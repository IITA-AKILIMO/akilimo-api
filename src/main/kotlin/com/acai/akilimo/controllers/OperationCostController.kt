package com.acai.akilimo.controllers

import com.acai.akilimo.mapper.OperationCostDto
import com.acai.akilimo.service.OperationCostService
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*


@RequestMapping("/api/v3/operation-cost")
@RestController
class OperationCostController(private val operationCostService: OperationCostService) : BaseController() {

    companion object {
        val logger = LoggerFactory.getLogger(OperationCostController::class.java)
    }

    @GetMapping
    fun listOperationCost(
            @RequestHeader("op-name") opName: String,
            @RequestHeader("op-type") opType: String
    ): ResponseEntity<List<OperationCostDto>> {

        val operationCostList = operationCostService.operationCost(opName, opType)

        return ResponseEntity(operationCostList, HttpStatus.OK)
    }
}