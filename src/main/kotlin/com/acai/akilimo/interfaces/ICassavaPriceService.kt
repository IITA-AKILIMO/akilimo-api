package com.acai.akilimo.interfaces


import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.ProducePriceDto
import com.acai.akilimo.request.ProducePriceRequest

interface ICassavaPriceService {

    fun cassavaPrices(countryCode: EnumCountry): List<ProducePriceDto>

    fun saveFertilizerPrice(producePriceRequest: ProducePriceRequest): ProducePriceDto?

    fun updateCassavaPrice(id: Long, producePriceRequest: ProducePriceRequest): ProducePriceDto?

    fun deleteCassavaPrice(id: Long): Boolean

}
