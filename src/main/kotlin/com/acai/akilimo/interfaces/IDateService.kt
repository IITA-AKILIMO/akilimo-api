package com.acai.akilimo.interfaces


import com.acai.akilimo.mapper.DateDto

interface IDateService {
    fun plantingDates(): List<DateDto>
    fun harvestDates(): List<DateDto>

//    fun saveFertilizerPrice(fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto?
//
//    fun updateFertilizerPrice(id: Long, fertilizerPriceRequest: FertilizerPriceRequest): FertilizerPriceDto?
//
//    fun deleteFertilizerPrice(id: Long): Boolean

}
