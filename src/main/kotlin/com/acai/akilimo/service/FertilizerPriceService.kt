package com.acai.akilimo.service


import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.interfaces.IFertilizerPriceService
import com.acai.akilimo.mapper.FertilizerPriceDto
import com.acai.akilimo.repositories.FertilizerPriceRepository
import com.acai.akilimo.request.FertilizerPriceRequest
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.modelmapper.convention.MatchingStrategies
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import org.springframework.transaction.annotation.Transactional
import java.util.*

@Service
class FertilizerPriceService
@Autowired
constructor(
        private val fertilizerPriceRepository: FertilizerPriceRepository,
        akilimoConfigProperties: AkilimoConfigProperties
) : IFertilizerPriceService {
    private val logger = LoggerFactory.getLogger(FertilizerPriceService::class.java)
    private val currencyProperties = akilimoConfigProperties.currency()

    val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    override fun fertilizers(countryCode: String): List<FertilizerPriceDto> {
//        val fertilizerList = fertilizerPriceRepository.findByActiveIsTrueOrderBySortOrderDesc()
        val fertilizerList = fertilizerPriceRepository.findByActiveIsTrueOrderBySortOrderAsc()
        val fertilizerPriceDtoList = ArrayList<FertilizerPriceDto>()

        var toCurrency = "USD"
        var currencyRate = 1.00
        val country = countryCode.toUpperCase()

        when (country) {
            "TZ" -> {
                toCurrency = "TZS"
                currencyRate = currencyProperties.tzsUsdRate!!
            }
            "NG" -> {
                toCurrency = "NGN"
                currencyRate = currencyProperties.ngnUsdRate!!
            }
        }


        for (fertilizerPrice in fertilizerList) {
            val fertilizerPriceDto = modelMapper.map(fertilizerPrice, FertilizerPriceDto::class.java)
            fertilizerPriceDto.priceRange = conversion.convertFertilizerPriceToLocalCurrency(
                    minUsd = fertilizerPrice.minUsd!!,
                    maxUsd = fertilizerPrice.maxUsd!!,
                    currencyRate = currencyRate,
                    toCurrency = toCurrency,
                    nearestValue = 1000.0)

            val pricePerBagRaw = conversion.convertToSpecifiedCurrency(fromAmount = fertilizerPrice.pricePerBag!!, exchangeRate = currencyRate)
            val pricePerBag = conversion.roundToNearestSpecifiedValue(pricePerBagRaw, 1000.00)
            fertilizerPriceDto.pricePerBag = pricePerBag
            fertilizerPriceDto.country = country
            fertilizerPriceDto.fertilizerCountry = "$country-$pricePerBagRaw"

            fertilizerPriceDtoList.add(fertilizerPriceDto)
        }

        return fertilizerPriceDtoList
    }

    override fun saveFertilizerPrice(fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto? {
        val entity = modelMapper.map(fertilizerPriceRequest, FertilizerPrices::class.java)

        val saved = fertilizerPriceRepository.save(entity)

        val resp = modelMapper.map(saved, FertilizerPriceDto::class.java)

        //default conversions
        resp.priceRange = conversion.convertFertilizerPriceToLocalCurrency(
                minUsd = saved.minUsd!!,
                maxUsd = saved.maxUsd!!,
                currencyRate = 1.00,
                toCurrency = "USD",
                nearestValue = 1000.0)

        return resp
    }

    override fun updateFertilizerPrice(id: Long, fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto? {
        val entity = fertilizerPriceRepository.findById(id).get()

        modelMapper.configuration.isSkipNullEnabled = true
        modelMapper.configuration.propertyCondition
        modelMapper.configuration.matchingStrategy = MatchingStrategies.STRICT


        //BeanUtils.copyProperties(fertilizerPriceRequest, entity)
        modelMapper.map(fertilizerPriceRequest, entity)


        val saved = fertilizerPriceRepository.save(entity)

        return modelMapper.map(saved, FertilizerPriceDto::class.java)
    }

    @Transactional
    override fun deleteFertilizerPrice(id: Long): Boolean {

        val entity = fertilizerPriceRepository.findByPriceId(id)

        return when {
            entity != null -> {
                fertilizerPriceRepository.deleteById(id)
                true
            }
            else -> false
        }

    }


}