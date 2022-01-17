package com.iita.akilimo.core.request.usecases.fr

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.core.request.FertilizerList
import com.iita.akilimo.core.request.UserInfo
import com.iita.akilimo.core.request.usecases.sp.SpComputeRequest


open class FrRequest(
    @JsonProperty("userInfo")
    var userInfo: UserInfo,
    @JsonProperty("computeRequest")
    var computeRequest: SpComputeRequest,
    @JsonProperty("fertilizerList")
    var fertilizerList: Set<FertilizerList>
) {
    var appLanguage: String? = null
}
