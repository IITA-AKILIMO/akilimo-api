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

    override fun fertilizerPriceByCountry(countryCode: String): List<FertilizerPriceDto> {
        val fertilizerList = fertilizerPriceRepository.findByPriceActiveIsTrueOrderBySortOrderAsc()
        val fertilizerPriceDtoList = ArrayList<FertilizerPriceDto>()

        var toCurrency = EnumCountry.ALL.currency()
        var currencyRate = 1.00
        val country = countryCode.toUpperCase()

        when (country) {
            EnumCountry.TZ.name -> {
                toCurrency = EnumCountry.TZ.currency()
                currencyRate = currencyProperties.tzsUsd!!
            }
            EnumCountry.NG.name -> {
                toCurrency = EnumCountry.NG.currency()
                currencyRate = currencyProperties.ngnUsd!!
            }
            EnumCountry.GH.name -> {
                toCurrency = EnumCountry.GH.currency()
                currencyRate = currencyProperties.ghsUsd!!
            }
        }


        val currencyEntity = currencyRepo.findByCurrencyCode(toCurrency)
        val currencyDto = modelMapper.map(currencyEntity, CurrencyDto::class.java)
        var sortIndex: Long = 1

        val minPrice = fertilizerPriceRepository.findBySortOrder(1)
        val maxPrice = fertilizerPriceRepository.findBySortOrder(4)

        val minAllowed = conversion.convertToSpecifiedCurrency(
            amount = minPrice.minUsd!!,
            currencyRate = currencyRate,
            currencyDto = currencyDto,
            nearestValue = 1000.0
        )

        val maxAllowed = conversion.convertToSpecifiedCurrency(
            amount = maxPrice.maxUsd!!,
            currencyRate = currencyRate,
            currencyDto = currencyDto,
            nearestValue = 1000.0
        )
        for (fertilizerPrice in fertilizerList) {
            val fertilizerPriceDto = modelMapper.map(fertilizerPrice, FertilizerPriceDto::class.java)

//            fertilizerPriceDto.minAllowedPrice = minAllowed
//            fertilizerPriceDto.maxAllowedPrice = maxAllowed

            fertilizerPriceDto.priceRange = conversion.convertPriceToLocalCurrency(
                minUsd = fertilizerPrice.minUsd!!,
                maxUsd = fertilizerPrice.maxUsd!!,
                currencyRate = currencyRate,
                currencyDto = currencyDto,
                nearestValue = 1000.0
            )

//            fertilizerPriceDto.minLocalPrice = conversion.convertToSpecifiedCurrency(
//                amount = fertilizerPrice.minUsd!!,
//                currencyRate = currencyRate,
//                currencyDto = currencyDto,
//                nearestValue = 1000.0
//            )
//
//            fertilizerPriceDto.maxLocalPrice = conversion.convertToSpecifiedCurrency(
//                amount = fertilizerPrice.maxUsd!!,
//                currencyRate = currencyRate,
//                currencyDto = currencyDto,
//                nearestValue = 1000.0
//            )

            val pricePerBagRaw = conversion.convertToSpecifiedCurrency(fromAmount = fertilizerPrice.pricePerBag!!, exchangeRate = currencyRate)
            val pricePerBag = conversion.roundToNearestSpecifiedValue(pricePerBagRaw, 1000.00)
            fertilizerPriceDto.priceId = sortIndex
            fertilizerPriceDto.id = fertilizerPrice.id!!
//            fertilizerPriceDto.pricePerBag = pricePerBag
            fertilizerPriceDto.country = country
            fertilizerPriceDto.fertilizerCountry = "$country$sortIndex"

            fertilizerPriceDtoList.add(fertilizerPriceDto)
            sortIndex++
        }

        return fertilizerPriceDtoList
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

    fun fertilizerPrices(fertilizerKey: String): List<FertilizerPriceDto> {
        val fertilizerPrices = fertilizerPriceRepo.findAllByFertilizerKeyAndPriceActiveIsTrueOrderBySortOrderAsc(fertilizerKey)

        val minPrice = fertilizerPriceRepo.findBySortOrder(1)
        val maxPrice = fertilizerPriceRepo.findBySortOrder(4)

        return fertilizerPrices.map { priceEntity ->
            val dto = modelMapper.map(priceEntity, FertilizerPriceDto::class.java)
            dto.minLocalPrice = minPrice.minPrice
            dto.maxLocalPrice = maxPrice.maxPrice

            dto.minAllowedPrice = maxPrice.minPrice
            dto.maxAllowedPrice = maxPrice.maxPrice

            dto.fertilizerCountry = "${priceEntity.country}${priceEntity.id}"
            dto
        }
    }


}
