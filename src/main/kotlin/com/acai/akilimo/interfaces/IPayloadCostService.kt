package com.acai.akilimo.interfaces


import com.acai.akilimo.mapper.PayloadDto

interface IPayloadCostService {

    fun payloadList(): List<PayloadDto>
    
    fun findPayloadByRequestId(requestId: String): PayloadDto
}
