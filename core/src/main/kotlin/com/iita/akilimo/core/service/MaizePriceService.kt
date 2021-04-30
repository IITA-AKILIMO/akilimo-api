package com.iita.akilimo.core.service


import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.core.utils.CurrencyConversion
import com.iita.akilimo.database.repos.MaizePriceRepository
import com.iita.akilimo.database.entities.MaizePrice
import com.iita.akilimo.enums.EnumCountry
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional

@Suppress("DuplicatedCode")
@Service
class MaizePriceService
constructor(
    val maizePriceRepository: MaizePriceRepository
) {
    private val logger = LoggerFactory.getLogger(MaizePriceService::class.java)

    private val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    fun fetchAllPrices(): List<ProducePriceDto> {
        val maizePriceList = maizePriceRepository.findAll()
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
        val maizePriceList = maizePriceRepository.findAllByPriceActiveIsFalse()
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


    fun findMaizePricePriceById(id: Long): ProducePriceDto {
        val maizePrice = maizePriceRepository.findById(id).get()
        return modelMapper.map(maizePrice, ProducePriceDto::class.java)
    }

    fun maizePrices(countryCode: EnumCountry): List<ProducePriceDto> {
        val maizePriceList = maizePriceRepository.findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(countryCode.name)
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

    fun maizePrices(countryCode: EnumCountry, produceType: String): List<ProducePriceDto> {
        val maizePriceList = maizePriceRepository.findByCountryAndProduceTypeAndPriceActiveIsTrueOrderBySortOrderAsc(countryCode.name, produceType)
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

    fun saveMaizePrice(producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = modelMapper.map(producePriceRequest, MaizePrice::class.java)

        val saved = maizePriceRepository.save(entity)

        return modelMapper.map(saved, ProducePriceDto::class.java)
    }

    fun updateMaizePrice(id: Long, producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = maizePriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        modelMapper.map(producePriceRequest, entity)

        val saved = maizePriceRepository.save(entity)

        return modelMapper.map(saved, ProducePriceDto::class.java)
    }

    @Transactional
    fun deleteMaizePrice(id: Long): Boolean {
        val entity = maizePriceRepository.findByPriceId(id)

        return when {
            entity != null -> {
                maizePriceRepository.deleteById(id)
                true
            }
            else -> false
        }

    }

}
