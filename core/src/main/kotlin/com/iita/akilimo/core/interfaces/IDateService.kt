package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.mapper.DateDto


interface IDateService {
    fun plantingDates(): List<DateDto>
    fun harvestDates(): List<DateDto>
}
