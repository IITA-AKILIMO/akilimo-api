package com.acai.akilimo.interfaces


import com.acai.akilimo.mapper.PayloadDto
import org.springframework.data.domain.Pageable

interface IPayloadCostService {

    fun findPayloadById(id: Long): PayloadDto?

    fun payloadList(pageable: Pageable): List<PayloadDto>

    fun findPayloadByRequestId(requestId: String): List<PayloadDto>
}
