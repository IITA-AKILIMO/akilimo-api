package com.acai.akilimo.mapper

import lombok.Data
import java.util.*

@Data
class FertilizerPriceInfoDto {

    var minLocalPrice: Double? = null

    var maxLocalPrice: Double? = null

    var fertilizerPriceList = ArrayList<FertilizerPriceDto>()
}
