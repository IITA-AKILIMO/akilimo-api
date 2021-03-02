package com.acai.akilimo.mapper


import java.util.*


class FertilizerPriceInfoDto {

    var minLocalPrice: Double? = null

    var maxLocalPrice: Double? = null

    var fertilizerPriceList = ArrayList<FertilizerPriceDto>()
}
