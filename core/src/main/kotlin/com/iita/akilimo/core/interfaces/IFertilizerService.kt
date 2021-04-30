package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.mapper.FertilizerDto
import com.iita.akilimo.core.request.FertilizerRequest


interface IFertilizerService {
    fun fertilizers(countryCode: String, useCase: String?): List<FertilizerDto>

    fun saveFertilizer(fertilizerRequest: FertilizerRequest): FertilizerDto?

    fun updateFertilizer(id: Long, fertilizerRequest: FertilizerRequest): FertilizerDto?

    fun deleteFertilizer(id: Long): Boolean

}
