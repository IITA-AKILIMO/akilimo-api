package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonInclude
import com.fasterxml.jackson.annotation.JsonProperty


@JsonInclude(JsonInclude.Include.NON_NULL)    //  ignore all null fields
open class RecommendationResponseDto : BaseDto() {

    var country: String? = null

    @JsonProperty("userPhoneCC")
    var mobileCountryCode: String? = null

    @JsonProperty("userPhoneNr")
    var mobileNumber: String? = null

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

    var responsePayload: Array<Any>? = null
}
