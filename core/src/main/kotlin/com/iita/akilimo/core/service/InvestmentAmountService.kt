package com.iita.akilimo.core.service


import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.core.mapper.CurrencyDto
import com.iita.akilimo.core.mapper.FertilizerPriceDto
import com.iita.akilimo.core.mapper.InvestmentAmountDto
import com.iita.akilimo.core.request.FertilizerPriceRequest
import com.iita.akilimo.core.utils.CurrencyConversion
import com.iita.akilimo.database.repos.FertilizerPriceRepository
import com.iita.akilimo.database.entities.FertilizerPrices
import com.iita.akilimo.database.repos.InvestmentAmountRepo
import com.iita.akilimo.enums.EnumCountry
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import org.springframework.util.MultiValueMap

@Service
class InvestmentAmountService
constructor(
    val investmentRepo: InvestmentAmountRepo,
    configProperties: AkilimoConfigProperties
) {
    private val logger = LoggerFactory.getLogger(InvestmentAmountService::class.java)
    private val currencyProperties = configProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    fun investmentAmountByCountry(countryCode: String): List<InvestmentAmountDto> {
        val investment = investmentRepo.findAllByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(countryCode.uppercase())

        return investment.map { investmentAmount ->
            val dto = modelMapper.map(investmentAmount, InvestmentAmountDto::class.java)
            dto
        }
    }


}
