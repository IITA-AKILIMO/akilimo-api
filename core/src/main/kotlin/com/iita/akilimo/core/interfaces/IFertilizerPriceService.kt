package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.mapper.FertilizerPriceDto
import com.iita.akilimo.core.request.FertilizerPriceRequest


interface IFertilizerPriceService {
    fun fertilizerPriceByCountry(countryCode: String): List<FertilizerPriceDto>

    fun saveFertilizerPrice(fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto?

    fun updateFertilizerPrice(id: Long, fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto?

    fun deleteFertilizerPrice(id: Long): Boolean

}
