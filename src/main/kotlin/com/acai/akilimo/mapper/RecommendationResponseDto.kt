package com.acai.akilimo.mapper

import com.acai.akilimo.entities.RecommendationRequest
import lombok.Data

//@Data
open class RecommendationResponseDto : RecommendationRequestDto() {

    /*var areaUnits: String? = null

    var area: Double? = null

    var plantingDate: String? = null

    var harvestDate: String? = null*/

    var reccomendationText:String?= null

}
