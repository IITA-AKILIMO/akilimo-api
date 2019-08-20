package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.AvailableFertilizers
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.interfaces.IFertilizerService
import com.acai.akilimo.mapper.FertilizerDto
import com.acai.akilimo.repositories.FertilizerRepository
import com.acai.akilimo.request.FertilizerRequest
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*

@Service
class FertilizerService
@Autowired
constructor(
        private val fertilizerRepository: FertilizerRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : IFertilizerService {
    private val logger = LoggerFactory.getLogger(FertilizerService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    override fun fertilizers(countryCode: String): List<FertilizerDto> {

        val countries = ArrayList<String>()
        countries.add(EnumCountry.ALL.name)
        countries.add(countryCode)

        val fertilizerList = fertilizerRepository.findByAvailableIsTrueAndCountryInOrderBySortOrderAscNameAsc(countries)
        val fertilizerPriceDtoList = ArrayList<FertilizerDto>()

        var currencyCode = EnumCountry.ALL.currency()
        val country = countryCode.toUpperCase()

        when (country) {
            EnumCountry.TZ.name -> {
                currencyCode = EnumCountry.TZ.currency()
            }
            EnumCountry.NG.name -> {
                currencyCode = EnumCountry.NG.currency()
            }
        }

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        for (fertilizer in fertilizerList) {
            val fertilizerDto = modelMapper.map(fertilizer, FertilizerDto::class.java)
            fertilizerDto.currency = currencyCode
            fertilizerDto.countryCode = country
            fertilizerDto.fertilizerCountry = "${fertilizer.type}-$country"

            fertilizerPriceDtoList.add(fertilizerDto)
        }

        return fertilizerPriceDtoList
    }

    override fun saveFertilizer(fertilizerRequest: FertilizerRequest): FertilizerDto? {
        val entity = modelMapper.map(fertilizerRequest, AvailableFertilizers::class.java)

        val saved = fertilizerRepository.save(entity)

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        return modelMapper.map(saved, FertilizerDto::class.java)
    }

    override fun updateFertilizer(id: Long, fertilizerRequest: FertilizerRequest): FertilizerDto? {
        val entity = fertilizerRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        modelMapper.map(fertilizerRequest, entity)


        val saved = fertilizerRepository.save(entity)

        return modelMapper.map(saved, FertilizerDto::class.java)
    }

    @Transactional
    override fun deleteFertilizer(id: Long): Boolean {

        val entity = fertilizerRepository.findByFertilizerId(id)

        return when {
            entity != null -> {
                fertilizerRepository.deleteById(id)
                true
            }
            else -> false
        }

    }


}