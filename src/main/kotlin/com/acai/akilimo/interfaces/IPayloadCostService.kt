package com.acai.akilimo.interfaces


import com.acai.akilimo.mapper.PayloadDto
import org.springframework.util.MultiValueMap

interface IPayloadCostService {

    fun findPayloadById(id: Long): PayloadDto?

    fun payloadList(): List<PayloadDto>

    fun findPayloadByRequestId(requestId: String): List<PayloadDto>
}
