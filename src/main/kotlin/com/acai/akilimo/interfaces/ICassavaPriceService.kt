package com.acai.akilimo.interfaces


import com.acai.akilimo.mapper.CassavaPriceDto
import com.acai.akilimo.request.CassavaPriceRequest

interface ICassavaPriceService {
    fun cassavaPrices(countryCode: String): List<CassavaPriceDto>

    fun saveFertilizerPrice(cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto?

    fun updateFertilizerPrice(id: Long, cassavaPriceRequest: CassavaPriceRequest): CassavaPriceDto?

    fun deleteCassavaPrice(id: Long): Boolean

}
