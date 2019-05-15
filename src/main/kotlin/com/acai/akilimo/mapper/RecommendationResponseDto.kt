package com.acai.akilimo.mapper

import lombok.Data

@Data
class RecommendationResponseDto {

    var areaUnits: String? = null

    var area: Double? = null

    var plantingDate: String? = null

    var harvestDate: String? = null

    var reccomendationText:String?= null

}
