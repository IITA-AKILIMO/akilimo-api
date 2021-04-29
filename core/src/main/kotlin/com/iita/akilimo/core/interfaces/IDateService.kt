package com.iita.akilimo.core.interfaces


import com.acai.akilimo.mapper.DateDto

interface IDateService {
    fun plantingDates(): List<DateDto>
    fun harvestDates(): List<DateDto>
}
