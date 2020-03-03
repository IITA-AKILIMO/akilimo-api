package com.acai.akilimo.interfaces


import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.entities.OperationCost
import com.acai.akilimo.entities.Payload
import com.acai.akilimo.mapper.FertilizerPriceDto
import com.acai.akilimo.mapper.OperationCostDto
import com.acai.akilimo.mapper.PayloadDto
import com.acai.akilimo.request.FertilizerPriceRequest
import com.acai.akilimo.request.OperationCostRequest

interface IPayloadCostService {

    fun payloadList(): List<PayloadDto>
    
    fun findPayloadByRequestId(requestId: Long): PayloadDto
}
