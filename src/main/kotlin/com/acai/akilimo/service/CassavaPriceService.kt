package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.CassavaPrices
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.interfaces.ICassavaPriceService
import com.acai.akilimo.mapper.CassavaPriceDto
import com.acai.akilimo.repositories.CassavaPriceRepository
import com.acai.akilimo.request.CassavaPriceRequest
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import org.springframework.util.MultiValueMap
import java.util.*

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

    fun findCassavaPriceById(id: Long): CassavaPriceDto {
        val apiUser = cassavaPriceRepository.findById(id).get()
        return modelMapper.map(apiUser, CassavaPriceDto::class.java)
    }

    fun cassavaPrices(countryCode: EnumCountry): List<CassavaPriceDto> {
        val cassavaPriceList = cassavaPriceRepository.findByCountryAndActiveIsTrueOrderByMinLocalPriceAsc(countryCode.name)
        val cassavaPriceDtoList = ArrayList<CassavaPriceDto>()

        var priceIndex: Long = 1
        for (cassavaPrice in cassavaPriceList) {
            val fertilizerPriceDto = modelMapper.map(cassavaPrice, CassavaPriceDto::class.java)
            fertilizerPriceDto.priceIndex = priceIndex
            cassavaPriceDtoList.add(fertilizerPriceDto)
            priceIndex++
        }
        return cassavaPriceDtoList
    }

    fun saveFertilizerPrice(cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto? {
        val entity = modelMapper.map(cassavaPriceRequest, CassavaPrices::class.java)

        val saved = cassavaPriceRepository.save(entity)

        val resp = modelMapper.map(saved, CassavaPriceDto::class.java)

        return resp
    }

    fun updateCassavaPrice(id: Long, cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto? {
        val entity = cassavaPriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        modelMapper.map(cassavaPriceRequest, entity)

        val saved = cassavaPriceRepository.save(entity)

        return modelMapper.map(saved, CassavaPriceDto::class.java)
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