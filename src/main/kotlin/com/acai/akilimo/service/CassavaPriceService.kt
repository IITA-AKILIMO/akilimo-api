package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.CassavaPrices
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.ProducePriceDto
import com.acai.akilimo.repositories.CassavaPriceRepository
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
class CassavaPriceService
@Autowired
constructor(
        private val cassavaPriceRepository: CassavaPriceRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) {
    private val logger = LoggerFactory.getLogger(CassavaPriceService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    fun fetchAllPrices(): List<ProducePriceDto> {
        val cassavaPriceList = cassavaPriceRepository.findAll()
        val cassavaPriceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in cassavaPriceList) {
            val fertilizerPriceDto = modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
            fertilizerPriceDto.priceIndex = priceIndex
            cassavaPriceDtoList.add(fertilizerPriceDto)
            priceIndex++
        }
        return cassavaPriceDtoList
    }

    fun fetchAllInactivePrices(): List<ProducePriceDto> {
        val cassavaPriceList = cassavaPriceRepository.findAllByActiveIsFalse()
        val cassavaPriceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in cassavaPriceList) {
            val fertilizerPriceDto = modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
            fertilizerPriceDto.priceIndex = priceIndex
            cassavaPriceDtoList.add(fertilizerPriceDto)
            priceIndex++
        }
        return cassavaPriceDtoList
    }


    fun findCassavaPriceById(id: Long): ProducePriceDto {
        val apiUser = cassavaPriceRepository.findById(id).get()
        return modelMapper.map(apiUser, ProducePriceDto::class.java)
    }

    fun cassavaPrices(countryCode: EnumCountry): List<ProducePriceDto> {
        val cassavaPriceList = cassavaPriceRepository.findByCountryAndActiveIsTrueOrderBySortOrderAsc(countryCode.name)
        val cassavaPriceDtoList = ArrayList<ProducePriceDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in cassavaPriceList) {
            val fertilizerPriceDto = modelMapper.map(cassavaPrice, ProducePriceDto::class.java)
            fertilizerPriceDto.priceIndex = priceIndex
            cassavaPriceDtoList.add(fertilizerPriceDto)
            priceIndex++
        }
        return cassavaPriceDtoList
    }

    fun saveFertilizerPrice(producePriceRequest: ProducePriceRequest): ProducePriceDto? {
        val entity = modelMapper.map(producePriceRequest, CassavaPrices::class.java)

        val saved = cassavaPriceRepository.save(entity)

        val resp = modelMapper.map(saved, ProducePriceDto::class.java)

        return resp
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