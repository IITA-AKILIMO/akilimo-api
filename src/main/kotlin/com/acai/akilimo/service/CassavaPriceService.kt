package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.CassavaPrices
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
import java.util.*

@Service
class CassavaPriceService
@Autowired
constructor(
        private val cassavaPriceRepository: CassavaPriceRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : ICassavaPriceService {
    private val logger = LoggerFactory.getLogger(CassavaPriceService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    override fun cassavaPrices(countryCode: String): List<CassavaPriceDto> {
        val cassavaPriceList = cassavaPriceRepository.findByCountryAndActiveIsTrueOrderByMinLocalPriceDesc(countryCode)
        val cassavaPriceDtoList = ArrayList<CassavaPriceDto>()

        for (cassavaPrice in cassavaPriceList) {
            val fertilizerPriceDto = modelMapper.map(cassavaPrice, CassavaPriceDto::class.java)

            cassavaPriceDtoList.add(fertilizerPriceDto)
        }
        return cassavaPriceDtoList
    }

    override fun saveFertilizerPrice(cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto? {
        val entity = modelMapper.map(cassavaPriceRequest, CassavaPrices::class.java)

        val saved = cassavaPriceRepository.save(entity)

        val resp = modelMapper.map(saved, CassavaPriceDto::class.java)

        return resp
    }

    override fun updateFertilizerPrice(id: Long, cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto? {
        val entity = cassavaPriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT
        modelMapper.map(cassavaPriceRequest, entity)

        val saved = cassavaPriceRepository.save(entity)

        return modelMapper.map(saved, CassavaPriceDto::class.java)
    }

    @Transactional
    override fun deleteCassavaPrice(id: Long): Boolean {

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