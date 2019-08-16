package com.acai.akilimo.interfaces


import com.acai.akilimo.mapper.FertilizerDto
import com.acai.akilimo.request.FertilizerRequest

interface IFertilizerService {

    fun fertilizers(countryCode: String): List<FertilizerDto>

    fun saveFertilizer(fertilizerRequest: FertilizerRequest): FertilizerDto?

    fun updateFertilizer(id: Long, fertilizerRequest: FertilizerRequest): FertilizerDto?

    fun deleteFertilizer(id: Long): Boolean

}
