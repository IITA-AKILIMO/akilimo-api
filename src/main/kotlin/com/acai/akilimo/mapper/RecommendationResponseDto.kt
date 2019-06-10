package com.acai.akilimo.mapper

import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.request.ComputeRequest
import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data
import java.time.LocalDateTime

@Data
open class RecommendationResponseDto {

    var updatedAt: LocalDateTime? = null
    var createdAt: LocalDateTime? = null

    var country: String? = null

    @JsonProperty("userPhoneCC")
    var mobileCountryCode: String? = null

    @JsonProperty("userPhoneNr")
    var mobileNumber: String? = null

    @JsonProperty("fullPhoneNumber")
    var fullPhoneNumber: String? = null

    @JsonProperty("userName")
    var userName: String? = null

    @JsonProperty("userEmail")
    var userEmail: String? = null

    @JsonProperty("FR")
    var fertilizerRecText: String? = null

    @JsonProperty("IC")
    var interCroppingRecText: String? = null

    @JsonProperty("PP")
    var plantingPracticeRecText: String? = null

    @JsonProperty("SP")
    var scheduledPlantingRecText: String? = null

    var hasResponse: Boolean = false

}
