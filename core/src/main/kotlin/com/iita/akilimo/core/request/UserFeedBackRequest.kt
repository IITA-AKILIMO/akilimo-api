package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumUserType

class UserFeedBackRequest(
    @JsonProperty("deviceToken")
    val deviceToken: String,

    @JsonProperty("akilimoUsage")
    val akilimoUsage: String,

    @JsonProperty("akilimoRecRating")
    val akilimoRecRating: Int,

    @JsonProperty("akilimoUsefulRating")
    val akilimoUsefulRating: Int,

    @JsonProperty("language")
    val language: String
) {
    @JsonProperty("userType")
    var userType: EnumUserType = EnumUserType.OTHER
}
