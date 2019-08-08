package com.acai.akilimo.service

import com.acai.akilimo.interfaces.IDateService
import com.acai.akilimo.mapper.DateDto
import com.acai.akilimo.repositories.FertilizerPriceRepository
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import java.util.*

@Service
class DateService
@Autowired
constructor(
        private val fertilizerPriceRepository: FertilizerPriceRepository
) : IDateService {
    private val logger = LoggerFactory.getLogger(DateService::class.java)


    private val modelMapper = ModelMapper()

    override fun plantingDates(): List<DateDto> {
        return getRangeList()
    }

    override fun harvestDates(): List<DateDto> {
        return getRangeList()
    }

    private fun getRangeList(): List<DateDto> {
        val dtoList = ArrayList<DateDto>()
//        dtoList.add(DateDto("-2 Months", -2))
//        dtoList.add(DateDto("-1 Months", -1))
        dtoList.add(DateDto("Current", 0))
        dtoList.add(DateDto("1 Months", 1))
        dtoList.add(DateDto("2 Months", 2))

        return dtoList
    }

}