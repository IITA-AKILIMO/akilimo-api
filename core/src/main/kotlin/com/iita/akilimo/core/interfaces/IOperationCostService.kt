package com.iita.akilimo.core.interfaces


import com.iita.akilimo.core.mapper.OperationCostDto
import com.iita.akilimo.core.request.OperationCostRequest
import com.iita.akilimo.database.entities.OperationCost

interface IOperationCostService {
    fun operationCost(id: Long): OperationCostDto?

    fun operationCostList(opName: String, opType: String): List<OperationCostDto>

    fun saveFertilizerPrice(operationCostRequest: OperationCostRequest): OperationCostRequest?

    fun updateOperationCost(id: Long, operationCostRequest: OperationCostRequest): OperationCost?

    fun deleteOperationCost(id: Long): Boolean

}
