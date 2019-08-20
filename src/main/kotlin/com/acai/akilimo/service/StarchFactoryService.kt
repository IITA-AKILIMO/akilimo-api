package com.acai.akilimo.service


import com.acai.akilimo.entities.StarchFactory
import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.interfaces.IStarchFactoryService
import com.acai.akilimo.mapper.FertilizerDto
import com.acai.akilimo.mapper.StarchFactoryDto
import com.acai.akilimo.repositories.StarchFactoryRepository
import com.acai.akilimo.request.StarchFactoryRequest
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*

@Service
class StarchFactoryService
@Autowired
constructor(private val starchFactoryRepository: StarchFactoryRepository) : IStarchFactoryService {
    private val logger = LoggerFactory.getLogger(StarchFactoryService::class.java)

    private val modelMapper = ModelMapper()

    override fun factories(countryCode: String): List<StarchFactoryDto> {

        val countries = ArrayList<String>()
        countries.add(EnumCountry.ALL.name)
        countries.add(countryCode)

        val factoryList = starchFactoryRepository.findByFactoryActiveIsTrueAndCountryInOrderBySortOrderAscFactoryNameAsc(countries)
        val factoryDtoList = ArrayList<StarchFactoryDto>()

        val country = countryCode.toUpperCase()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        for (starchFactory in factoryList) {
            val starchFactoryDto = modelMapper.map(starchFactory, StarchFactoryDto::class.java)
            var factoryNameCountry: String = ""
            starchFactoryDto.countryCode = country

            factoryNameCountry = when {
                starchFactory.factoryName.equals("NA") -> "${starchFactory.factoryName}-${countryCode}"
                else -> "${starchFactory.factoryName}-${starchFactory.country}"
            }

            starchFactoryDto.factoryNameCountry = factoryNameCountry

            factoryDtoList.add(starchFactoryDto)
        }

        return factoryDtoList
    }

    override fun saveFactory(starchFactoryRequest: StarchFactoryRequest): StarchFactoryDto? {
        val entity = modelMapper.map(starchFactoryRequest, StarchFactory::class.java)

        val saved = starchFactoryRepository.save(entity)

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        return modelMapper.map(saved, StarchFactoryDto::class.java)
    }

    override fun updateFactory(id: Long, starchFactoryRequest: StarchFactoryRequest): StarchFactoryDto? {
        val entity = starchFactoryRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        modelMapper.map(starchFactoryRequest, entity)

        val saved = starchFactoryRepository.save(entity)

        return modelMapper.map(saved, StarchFactoryDto::class.java)
    }

    @Transactional
    override fun deleteFactory(id: Long): Boolean {
        return run {
            starchFactoryRepository.deleteById(id)
            true
        }
    }


}
