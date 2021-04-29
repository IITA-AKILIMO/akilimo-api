package com.iita.akilimo.core.interfaces


import com.acai.akilimo.mapper.FertilizerDto
import com.acai.akilimo.mapper.StarchFactoryDto
import com.acai.akilimo.request.FertilizerRequest
import com.acai.akilimo.request.StarchFactoryRequest

interface IStarchFactoryService {

    fun factories(countryCode: String): List<StarchFactoryDto>

    fun saveFactory(starchFactoryRequest: StarchFactoryRequest): StarchFactoryDto?

    fun updateFactory(id: Long, starchFactoryRequest: StarchFactoryRequest): StarchFactoryDto?

    fun deleteFactory(id: Long): Boolean

}
