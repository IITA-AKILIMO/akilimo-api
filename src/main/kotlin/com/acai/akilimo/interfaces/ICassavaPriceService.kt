package com.acai.akilimo.interfaces


import com.acai.akilimo.enums.EnumCountry
import com.acai.akilimo.mapper.CassavaPriceDto
import com.acai.akilimo.request.CassavaPriceRequest

interface ICassavaPriceService {

    fun cassavaPrices(countryCode: EnumCountry): List<CassavaPriceDto>

    fun saveFertilizerPrice(cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto?

    fun updateCassavaPrice(id: Long, cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto?

    fun deleteCassavaPrice(id: Long): Boolean

}
