package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.mapper.ProducePriceDto
import com.iita.akilimo.core.request.ProducePriceRequest
import com.iita.akilimo.enums.EnumCountry


interface ICassavaPriceService {

    fun cassavaPrices(countryCode: EnumCountry): List<ProducePriceDto>

    fun saveFertilizerPrice(producePriceRequest: ProducePriceRequest): ProducePriceDto?

    fun updateCassavaPrice(id: Long, producePriceRequest: ProducePriceRequest): ProducePriceDto?

    fun deleteCassavaPrice(id: Long): Boolean

}
