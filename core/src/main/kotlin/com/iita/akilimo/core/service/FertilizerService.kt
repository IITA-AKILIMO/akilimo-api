package com.iita.akilimo.core.service


import com.iita.akilimo.core.interfaces.IFertilizerService
import com.iita.akilimo.core.mapper.FertilizerDto
import com.iita.akilimo.core.request.FertilizerRequest
import com.iita.akilimo.database.entities.FertilizerEntity
import com.iita.akilimo.database.repos.FertilizerRepo
import com.iita.akilimo.database.repos.FertilizerPriceRepository
import com.iita.akilimo.enums.EnumCountry
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*
import kotlin.collections.ArrayList

@Service
class FertilizerService
constructor(
    val fertilizerRepo: FertilizerRepo,
    val priceRepo: FertilizerPriceRepository
) : IFertilizerService {
    private val logger = LoggerFactory.getLogger(FertilizerService::class.java)

    private val modelMapper = ModelMapper()

    override fun fertilizers(countryCode: String, useCase: String?): List<FertilizerDto> {

        val countries = ArrayList<String>()
        countries.add(EnumCountry.ALL.name)
        countries.add(countryCode)


        val fertilizerList = if (useCase != null) {
            fertilizerRepo.findByAvailableIsTrueAndCountryInAndUseCaseOrderBySortOrderAscNameAsc(countries, useCase)
        } else {
            fertilizerRepo.findByAvailableIsTrueAndCountryInOrderBySortOrderAscNameAsc(countries)
        }

        val fertilizerPriceDtoList = ArrayList<FertilizerDto>()

        var currencyCode = EnumCountry.ALL.currency()
        val country = countryCode.uppercase(Locale.getDefault())
        when (country) {
            EnumCountry.TZ.name -> {
                currencyCode = EnumCountry.TZ.currency()
            }
            EnumCountry.NG.name -> {
                currencyCode = EnumCountry.NG.currency()
            }
            EnumCountry.RW.name -> {
                currencyCode = EnumCountry.RW.currency()
            }
            EnumCountry.GH.name -> {
                currencyCode = EnumCountry.GH.currency()
            }
        }

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        for (fertilizer in fertilizerList) {
            val fertilizerDto = modelMapper.map(fertilizer, FertilizerDto::class.java)
            fertilizerDto.currency = currencyCode
            fertilizerDto.countryCode = country
            fertilizerDto.fertilizerCountry = "${fertilizer.fertilizerType}-$country"

            fertilizerPriceDtoList.add(fertilizerDto)
        }

        return fertilizerPriceDtoList
    }

    override fun saveFertilizer(fertilizerRequest: FertilizerRequest): FertilizerDto? {
        val entity = modelMapper.map(fertilizerRequest, FertilizerEntity::class.java)

        val saved = fertilizerRepo.save(entity)

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        return modelMapper.map(saved, FertilizerDto::class.java)
    }

    override fun updateFertilizer(id: Long, fertilizerRequest: FertilizerRequest): FertilizerDto? {
        val entity = fertilizerRepo.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        modelMapper.map(fertilizerRequest, entity)


        val saved = fertilizerRepo.save(entity)

        return modelMapper.map(saved, FertilizerDto::class.java)
    }

    @Transactional
    override fun deleteFertilizer(id: Long): Boolean {

        val entity = fertilizerRepo.findById(id)

        return run {
            fertilizerRepo.deleteById(id)
            true
        }

    }


}
