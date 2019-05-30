package com.acai.akilimo.mapper

import com.acai.akilimo.entities.RecommendationRequest
import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data
import java.time.LocalDateTime

@Data
open class RecommendationResponseDto : RecommendationRequestDto() {

    /*var areaUnits: String? = null

    var area: Double? = null

    var plantingDate: String? = null

    var harvestDate: String? = null*/

    var updatedAt: LocalDateTime? = null
    var createdAt: LocalDateTime? = null

    @JsonProperty("FR")
    var fertilizerRecText:String?= null

    @JsonProperty("IC")
    var interCroppingRecText:String?= null

    @JsonProperty("PP")
    var plantingPracticeRecText:String?= null

    @JsonProperty("SP")
    var scheduledPlantingRec:String?= null

}
