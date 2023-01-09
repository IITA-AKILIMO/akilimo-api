package com.iita.akilimo.core.service


import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.database.entities.CassavaPrice
import com.iita.akilimo.database.repos.CassavaPriceRepository

import com.iita.akilimo.enums.EnumCountry
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional

@Suppress("DuplicatedCode")
@Service
class CassavaPriceService
constructor(
    val cassavaPriceRepository: CassavaPriceRepository
) {
    private val logger = LoggerFactory.getLogger(CassavaPriceService::class.java)
    private val modelMapper = ModelMapper()

    fun fetchAllPrices(): List<ProducePriceDto> {
        val priceList = cassavaPriceRepository.findAll()
        val priceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in priceList) {
            val priceDto = modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
            priceDto.priceIndex = priceIndex
            priceDtoList.add(priceDto)
            priceIndex++
        }
        return priceDtoList
    }

    fun fetchAllInactivePrices(): List<ProducePriceDto> {
        val priceList = cassavaPriceRepository.findAllByPriceActiveIsFalse()
        val priceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in priceList) {
            val priceDto = modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
            priceDto.priceIndex = priceIndex
            priceDtoList.add(priceDto)
            priceIndex++
        }
        return priceDtoList
    }


    fun findCassavaPriceById(id: Long): ProducePriceDto {
        val cassavaPrice = cassavaPriceRepository.findById(id).get()
        return modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
    }

    fun cassavaPrices(countryCode: EnumCountry): List<ProducePriceDto> {
        val priceList = cassavaPriceRepository.findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(countryCode.name)
        val priceDtoList = ArrayList<ProducePriceDto>()

        val minPrice = cassavaPriceRepository.findFirstByCountryAndMinPriceTrue(countryCode.name)
        val maxPrice = cassavaPriceRepository.findFirstByCountryAndMaxPriceTrue(countryCode.name)

        var priceIndex: Long = 1
        for (cassavaPrice in priceList) {
            val priceDto = modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
            priceDto.priceIndex = priceIndex
            if (minPrice != null) {
                priceDto.minAllowedPrice = minPrice.minLocalPrice!!
            }
            if (maxPrice != null) {
                priceDto.maxAllowedPrice = maxPrice.maxLocalPrice!!
            }
            priceDtoList.add(priceDto)
            priceIndex++
        }
        return priceDtoList
    }

    fun saveFertilizerPrice(producePriceRequest: ProducePriceRequest): ProducePriceDto? {

        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        val entity = modelMapper.map(producePriceRequest, CassavaPrice::class.java)

        val saved = cassavaPriceRepository.save(entity)

        return modelMapper.map(saved, ProducePriceDto::class.java)
    }

    fun updateCassavaPrice(id: Long, producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = cassavaPriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        modelMapper.map(producePriceRequest, entity)

        val saved = cassavaPriceRepository.save(entity)

        return modelMapper.map(saved, ProducePriceDto::class.java)
    }

    @Transactional
    fun deleteCassavaPrice(id: Long): Boolean {

        val entity = cassavaPriceRepository.findByPriceId(id)

        return when {
            entity != null -> {
                cassavaPriceRepository.deleteById(id)
                true
            }
            else -> false
        }

    }

}
