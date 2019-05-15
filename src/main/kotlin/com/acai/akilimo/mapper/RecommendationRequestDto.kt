package com.acai.akilimo.mapper

import lombok.Data

@Data
class RecommendationRequestDto {

    var areaUnits: String? = null

    var area: Double? = null

    var plantingDate: String? = null

    var harvestDate: String? = null

}
