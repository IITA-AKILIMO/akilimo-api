package com.iita.akilimo.core.service


import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.core.interfaces.IFertilizerPriceService
import com.iita.akilimo.core.mapper.CurrencyDto
import com.iita.akilimo.core.mapper.FertilizerPriceDto
import com.iita.akilimo.core.request.FertilizerPriceRequest
import com.iita.akilimo.core.utils.CurrencyConversion
import com.iita.akilimo.database.repos.CurrencyRepo
import com.iita.akilimo.database.repos.FertilizerPriceRepository
import com.iita.akilimo.database.entities.FertilizerPrices
import com.iita.akilimo.database.repos.FertilizerPriceRepo
import com.iita.akilimo.enums.EnumCountry
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional

@Service
class FertilizerPriceService
constructor(
    val fertilizerPriceRepository: FertilizerPriceRepository,
    val fertilizerPriceRepo: FertilizerPriceRepo,
    val currencyRepo: CurrencyRepo,
    configProperties: AkilimoConfigProperties
) : IFertilizerPriceService {
    private val logger = LoggerFactory.getLogger(FertilizerPriceService::class.java)
    private val currencyProperties = configProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    override fun fertilizerPrices(fertilizerKey: String, countryCode: String): List<FertilizerPriceDto> {

        var toCurrency = EnumCountry.ALL.currency()
        val currencyRate = 1.00
        when (countryCode) {
            EnumCountry.TZ.name -> {
                toCurrency = EnumCountry.TZ.currency()
//                currencyRate = currencyProperties.tzsUsd!!
            }
            EnumCountry.NG.name -> {
                toCurrency = EnumCountry.NG.currency()
//                currencyRate = currencyProperties.ngnUsd!!
            }
            EnumCountry.GH.name -> {
                toCurrency = EnumCountry.GH.currency()
//                currencyRate = currencyProperties.ghsUsd!!
            }
        }


        val currencyEntity = currencyRepo.findByCurrencyCode(toCurrency)
        val currencyDto = modelMapper.map(currencyEntity, CurrencyDto::class.java)

        val fertilizerPrices = fertilizerPriceRepo.findAllByFertilizerKeyAndPriceActiveIsTrueOrderBySortOrderAsc(fertilizerKey)

        val minPrice = fertilizerPriceRepo.findBySortOrderAndFertilizerKey(1, fertilizerKey)
        val maxPrice = fertilizerPriceRepo.findBySortOrderAndFertilizerKey(4, fertilizerKey)

        return fertilizerPrices.map { priceEntity ->
            val dto = modelMapper.map(priceEntity, FertilizerPriceDto::class.java)
            dto.minLocalPrice = minPrice.minPrice
            dto.maxLocalPrice = maxPrice.maxPrice

            dto.minAllowedPrice = maxPrice.minPrice
            dto.maxAllowedPrice = maxPrice.maxPrice

            dto.priceRange = conversion.convertPriceToLocalCurrency(
                minUsd = priceEntity.pricePerBag!!.toDouble(),
                maxUsd = priceEntity.pricePerBag!!.toDouble(),
                currencyRate = currencyRate,
                currencyDto = currencyDto,
                nearestValue = 1000.0
            )

            dto.fertilizerCountry = "${priceEntity.country}${priceEntity.id}"
            dto
        }
    }

    override fun saveFertilizerPrice(fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto? {
        val entity = modelMapper.map(fertilizerPriceRequest, FertilizerPrices::class.java)

        val saved = fertilizerPriceRepository.save(entity)

        val resp = modelMapper.map(saved, FertilizerPriceDto::class.java)

        val currencyEntity = currencyRepo.findByCurrencyCode(fertilizerPriceRequest.currencyCode)
        val currencyDto = modelMapper.map(currencyEntity, CurrencyDto::class.java)

        resp.priceRange = conversion.convertPriceToLocalCurrency(
            minUsd = saved.minUsd!!,
            maxUsd = saved.maxUsd!!,
            currencyRate = 1.00,
            currencyDto = currencyDto,
            nearestValue = 1000.0
        )

        return resp
    }

    override fun updateFertilizerPrice(id: Long, fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto? {
        val entity = fertilizerPriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT

        modelMapper.map(fertilizerPriceRequest, entity)


        val saved = fertilizerPriceRepository.save(entity)

        return modelMapper.map(saved, FertilizerPriceDto::class.java)
    }

    @Transactional
    override fun deleteFertilizerPrice(id: Long): Boolean {

        val entity = fertilizerPriceRepository.findById(id)

        return run {
            fertilizerPriceRepository.deleteById(id)
            true
        }

    }


}
