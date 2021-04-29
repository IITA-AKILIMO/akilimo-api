package com.iita.akilimo.core.service


import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.core.utils.CurrencyConversion
import com.iita.akilimo.database.repos.PotatoPriceRepository
import com.iita.akilimo.database.repos.PotatoPrices
import com.iita.akilimo.enums.EnumCountry
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional

@Suppress("DuplicatedCode")
@Service
class PotatoPriceService
@Autowired
constructor(
        private val potatoPriceRepository: PotatoPriceRepository
) {
    private val logger = LoggerFactory.getLogger(PotatoPriceService::class.java)

    private val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    fun fetchAllPrices(): List<ProducePriceDto> {
        val maizePriceList = potatoPriceRepository.findAll()
        val priceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (maizePrice in maizePriceList) {
            val priceDto = modelMapper.map(maizePrice, ProducePriceDto::class.java)
            priceDto.priceIndex = priceIndex
            priceDtoList.add(priceDto)
            priceIndex++
        }
        return priceDtoList
    }

    fun fetchAllInactivePrices(): List<ProducePriceDto> {
        val potatoPriceList = potatoPriceRepository.findAllByPriceActiveIsFalse()
        val priceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (potatoPrices in potatoPriceList) {
            val priceDto = modelMapper.map(potatoPrices, ProducePriceDto::class.java)
            priceDto.priceIndex = priceIndex
            priceDtoList.add(priceDto)
            priceIndex++
        }
        return priceDtoList
    }


    fun findPotatoPriceById(id: Long): ProducePriceDto {
        val maizePrice = potatoPriceRepository.findById(id).get()
        return modelMapper.map(maizePrice, ProducePriceDto::class.java)
    }

    fun potatoPrices(countryCode: EnumCountry): List<ProducePriceDto> {
        val potatoPriceList = potatoPriceRepository.findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(countryCode.name)
        val priceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (potatoPrice in potatoPriceList) {
            val priceDto = modelMapper.map(potatoPrice, ProducePriceDto::class.java)
            priceDto.priceIndex = priceIndex
            priceDtoList.add(priceDto)
            priceIndex++
        }
        return priceDtoList
    }

    fun savePotatoPrice(producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = modelMapper.map(producePriceRequest, PotatoPrices::class.java)

        val saved = potatoPriceRepository.save(entity)

        return modelMapper.map(saved, ProducePriceDto::class.java)
    }

    fun updatePotatoPrice(id: Long, producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = potatoPriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        modelMapper.map(producePriceRequest, entity)

        val saved = potatoPriceRepository.save(entity)

        return modelMapper.map(saved, ProducePriceDto::class.java)
    }

    @Transactional
    fun deletePotatoPrice(id: Long): Boolean {

        val entity = potatoPriceRepository.findByPriceId(id)

        return when {
            entity != null -> {
                potatoPriceRepository.deleteById(id)
                true
            }
            else -> false
        }

    }

}
