package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.mapper.StarchFactoryDto
import com.iita.akilimo.core.request.StarchFactoryRequest


interface IStarchFactoryService {

    fun factories(countryCode: String): List<StarchFactoryDto>

    fun saveFactory(starchFactoryRequest: StarchFactoryRequest): StarchFactoryDto?

    fun updateFactory(id: Long, starchFactoryRequest: StarchFactoryRequest): StarchFactoryDto?

    fun deleteFactory(id: Long): Boolean

}
