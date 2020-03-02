package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.interfaces.IPayloadCostService
import com.acai.akilimo.mapper.PayloadDto
import com.acai.akilimo.repositories.PayloadRepository
import com.acai.akilimo.utils.CurrencyConversion
import com.fasterxml.jackson.core.type.TypeReference
import org.modelmapper.ModelMapper
import org.modelmapper.TypeToken
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service


@Service
class PayloadService
@Autowired
constructor(
        private val operationCostRepository: PayloadRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : IPayloadCostService {
    private val logger = LoggerFactory.getLogger(PayloadService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()


    override fun payloadList(): List<PayloadDto> {
        val operationCostList = operationCostRepository.findAll()

//        val myObjects: List<PayloadDto> = modelMapper.map(operationCostList, object : TypeReference<List<PayloadDto() {})

        val h = modelMapper.map(operationCostList, TypeReference < List < PayloadDto::class.java > >{})
    }

}