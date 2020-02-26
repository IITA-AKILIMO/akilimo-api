package com.acai.akilimo.interfaces


import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.entities.OperationCost
import com.acai.akilimo.mapper.FertilizerPriceDto
import com.acai.akilimo.mapper.OperationCostDto
import com.acai.akilimo.request.FertilizerPriceRequest
import com.acai.akilimo.request.OperationCostRequest

interface IOperationCostService {
    fun operationCost(opName: String, opType: String): List<OperationCostDto>

    fun saveFertilizerPrice(operationCostRequest: OperationCostRequest): OperationCostRequest?

    fun updateOperationCost(id: Long, operationCostRequest: OperationCostRequest): OperationCost?

    fun deleteOperationCost(id: Long): Boolean

}
