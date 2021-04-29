package com.iita.akilimo.core.service


import com.iita.akilimo.core.mapper.CurrencyDto
import com.iita.akilimo.database.repos.CurrencyRepo
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service

@Suppress("DuplicatedCode")
@Service
class CurrencyService
@Autowired
constructor(
    private val currencyRepo: CurrencyRepo
) {
    private val logger = LoggerFactory.getLogger(CurrencyService::class.java)
    private val modelMapper = ModelMapper()

    fun fetchAllCurrencies(): List<CurrencyDto> {
        val currencyList = currencyRepo.findAll()
        val currencyDtoList = ArrayList<CurrencyDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in currencyList) {
            val currencyDto = modelMapper.map(cassavaPrice, CurrencyDto::class.java)
            currencyDtoList.add(currencyDto)
            priceIndex++
        }
        return currencyDtoList
    }
}
