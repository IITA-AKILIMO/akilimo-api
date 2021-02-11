package com.acai.akilimo.service


import com.acai.akilimo.entities.MaizePrices
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.ProducePriceDto
import com.acai.akilimo.repositories.MaizePriceRepository
import com.acai.akilimo.request.ProducePriceRequest
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*

@Suppress("DuplicatedCode")
@Service
class MaizePriceService
@Autowired
constructor(
    private val maizePriceRepository: MaizePriceRepository
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
        val maizePriceList = maizePriceRepository.findAllByActiveIsFalse()
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
        val maizePriceList = maizePriceRepository.findByCountryAndActiveIsTrueOrderBySortOrderAsc(countryCode.name)
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
        val maizePriceList = maizePriceRepository.findByCountryAndProduceTypeAndActiveIsTrueOrderBySortOrderAsc(countryCode.name, produceType)
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
        val entity = modelMapper.map(producePriceRequest, MaizePrices::class.java)

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