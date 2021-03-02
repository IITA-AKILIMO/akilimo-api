package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty



open class RecommendationRequest(
        @JsonProperty("userInfo")
        var userInfo: UserInfo,
        @JsonProperty("computeRequest")
        var computeRequest: ComputeRequest,
        @JsonProperty("fertilizerList")
        var fertilizerList: Set<FertilizerList>
) {
    var appLanguage: String? = null
}