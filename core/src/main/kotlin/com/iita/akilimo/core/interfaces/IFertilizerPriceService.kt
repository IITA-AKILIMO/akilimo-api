package com.iita.akilimo.core.interfaces


import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.mapper.FertilizerPriceDto
import com.acai.akilimo.request.FertilizerPriceRequest

interface IFertilizerPriceService {
    fun fertilizerPriceByCountry(countryCode: String): List<FertilizerPriceDto>

    fun saveFertilizerPrice(fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto?

    fun updateFertilizerPrice(id: Long, fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto?

    fun deleteFertilizerPrice(id: Long): Boolean

}
