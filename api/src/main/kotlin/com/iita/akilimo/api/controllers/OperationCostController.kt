package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.OperationCostDto
import com.iita.akilimo.core.service.OperationCostService
import io.swagger.v3.oas.annotations.Hidden
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*

@Hidden
@RequestMapping(value = ["/api/v1/operation-cost", "/api/v3/operation-cost"])
@RestController
class OperationCostController(private val operationCostService: OperationCostService) : BaseController() {

    companion object {
        val logger = LoggerFactory.getLogger(OperationCostController::class.java)
    }

    @GetMapping("/{id}")
    fun listOperationCost(@PathVariable id: Long): ResponseEntity<OperationCostDto> {
        val operationCostList = operationCostService.operationCost(id)
        return ResponseEntity(operationCostList, HttpStatus.OK)
    }

    @GetMapping
    fun listOperationCosts(
        @RequestHeader("op-name") opName: String,
        @RequestHeader("op-type") opType: String
    ): ResponseEntity<List<OperationCostDto>> {

        val operationCostList = operationCostService.operationCostList(opName.toLowerCase(), opType.toLowerCase())

        return ResponseEntity(operationCostList, HttpStatus.OK)
    }
}
