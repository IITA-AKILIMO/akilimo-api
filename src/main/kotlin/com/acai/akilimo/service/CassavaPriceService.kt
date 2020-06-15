package com.acai.akilimo.service


import com.acai.akilimo.entities.CassavaPrices
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.ProducePriceDto
import com.acai.akilimo.repositories.CassavaPriceRepository
import com.acai.akilimo.request.ProducePriceRequest
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*

@Suppress("DuplicatedCode")
@Service
class CassavaPriceService
@Autowired
constructor(
        private val cassavaPriceRepository: CassavaPriceRepository
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
        val priceList = cassavaPriceRepository.findAllByActiveIsFalse()
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
        val priceList = cassavaPriceRepository.findByCountryAndActiveIsTrueOrderBySortOrderAsc(countryCode.name)
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

    fun saveFertilizerPrice(producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = modelMapper.map(producePriceRequest, CassavaPrices::class.java)

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