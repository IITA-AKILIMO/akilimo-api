package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.interfaces.IPayloadCostService
import com.acai.akilimo.mapper.PayloadDto
import com.acai.akilimo.repositories.PayloadRepository
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import java.util.*


@Service
class PayloadService
@Autowired
constructor(
        private val payloadRepository: PayloadRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : IPayloadCostService {
    private val logger = LoggerFactory.getLogger(PayloadService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()


    override fun payloadList(): List<PayloadDto> {
        val payloadList = payloadRepository.findAll()
        val payloadDtoList = ArrayList<PayloadDto>()
        for (payload in payloadList) {
            val payloadDto = modelMapper.map(payload, PayloadDto::class.java)
            payloadDtoList.add(payloadDto)
        }

        return payloadDtoList
    }

    override fun findPayloadByRequestId(requestId: String): PayloadDto {
        val payload = payloadRepository.findByRequestId(requestId)
        return modelMapper.map(payload, PayloadDto::class.java)
    }

}