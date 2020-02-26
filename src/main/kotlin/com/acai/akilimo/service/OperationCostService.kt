package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.entities.OperationCost
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.interfaces.IFertilizerPriceService
import com.acai.akilimo.interfaces.IOperationCostService
import com.acai.akilimo.mapper.FertilizerPriceDto
import com.acai.akilimo.mapper.OperationCostDto
import com.acai.akilimo.repositories.FertilizerPriceRepository
import com.acai.akilimo.repositories.OperationCostRepository
import com.acai.akilimo.request.FertilizerPriceRequest
import com.acai.akilimo.request.OperationCostRequest
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*

@Service
class OperationCostService
@Autowired
constructor(
        private val operationCostRepository: OperationCostRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : IOperationCostService {
    private val logger = LoggerFactory.getLogger(OperationCostService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()
    override fun operationCost(opName: String, opType: String): List<OperationCostDto> {
        val operationCostList = operationCostRepository.findByActiveIsTrueAndOperationNameAndOperationType(opName, opType)
        val priceDtoList = ArrayList<OperationCostDto>()

        for (operationCost in operationCostList) {
            val operationCostDto = modelMapper.map(operationCost, OperationCostDto::class.java)
            priceDtoList.add(operationCostDto);
        }
        return priceDtoList
    }

    override fun saveFertilizerPrice(operationCostRequest: OperationCostRequest): OperationCostRequest? {
        TODO("not implemented") //To change body of created functions use File | Settings | File Templates.
    }

    override fun updateOperationCost(id: Long, operationCostRequest: OperationCostRequest): OperationCost? {
        TODO("not implemented") //To change body of created functions use File | Settings | File Templates.
    }

    override fun deleteOperationCost(id: Long): Boolean {
        TODO("not implemented") //To change body of created functions use File | Settings | File Templates.
    }

}