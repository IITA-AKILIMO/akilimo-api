package com.iita.akilimo.core.service


import com.fasterxml.jackson.databind.JsonNode
import com.fasterxml.jackson.databind.ObjectMapper
import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.core.interfaces.IPayloadCostService
import com.iita.akilimo.core.mapper.PayloadDto
import com.iita.akilimo.core.utils.CurrencyConversion
import com.iita.akilimo.database.entities.Payload
import com.iita.akilimo.database.repos.PayloadRepository
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.stereotype.Service


@Service
class PayloadService
constructor(
        val payloadRepository: PayloadRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : IPayloadCostService {
    private val logger = LoggerFactory.getLogger(PayloadService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()
    private val mapper = ObjectMapper()


    override fun findPayloadById(id: Long): PayloadDto? {
        val payload = payloadRepository.findByIdOrderByCreatedAtAsc(id)

        if (payload != null) {
            val droidRequest: JsonNode = mapper.readTree(payload.droidRequest)
            val plumberRequest: JsonNode = mapper.readTree(payload.plumberRequest)
            val plumberResponse: JsonNode = mapper.readTree(payload.plumberResponse)

            val payloadDto = modelMapper.map(payload, PayloadDto::class.java)

            payloadDto.droidRequest = droidRequest
            payloadDto.plumberRequest = plumberRequest
            payloadDto.plumberResponse = plumberResponse

            return payloadDto
        }
        return null
    }


    override fun payloadList(pageable: Pageable): Page<PayloadDto> {

        val payloadList = payloadRepository.findAllByOrderByUpdatedAtDesc(pageable = pageable)

        return payloadList.map { payload ->
            val payloadDto = modelMapper.map(payload, PayloadDto::class.java)

            val droidRequest: JsonNode = mapper.readTree(payload.droidRequest)
            val plumberRequest: JsonNode = mapper.readTree(payload.plumberRequest)
            val plumberResponse: JsonNode = mapper.readTree(payload.plumberResponse)

            payloadDto.droidRequest = droidRequest
            payloadDto.plumberRequest = plumberRequest
            payloadDto.plumberResponse = plumberResponse

            payloadDto
        }
    }

    override fun findPayloadByRequestId(requestId: String): List<PayloadDto> {
        val payloadList = payloadRepository.findAllByRequestId(requestId)
        return processListResponse(payloadList)
    }

    private fun processListResponse(payloadList: List<Payload>): List<PayloadDto> {
        val payloadDtoList = ArrayList<PayloadDto>()

        for (payload in payloadList) {
            val payloadDto = modelMapper.map(payload, PayloadDto::class.java)

            val droidRequest: JsonNode = mapper.readTree(payload.droidRequest)
            val plumberRequest: JsonNode = mapper.readTree(payload.plumberRequest)
            val plumberResponse: JsonNode = mapper.readTree(payload.plumberResponse)

            payloadDto.droidRequest = droidRequest
            payloadDto.plumberRequest = plumberRequest
            payloadDto.plumberResponse = plumberResponse

            payloadDtoList.add(payloadDto)
        }
        return payloadDtoList;
    }


}
