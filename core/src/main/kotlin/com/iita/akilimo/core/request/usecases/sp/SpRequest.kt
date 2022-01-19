package com.iita.akilimo.core.request.usecases.sp

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.core.request.FertilizerList
import com.iita.akilimo.core.request.UserInfo
import com.iita.akilimo.core.request.usecases.bpp.BppComputeRequest


open class SpRequest(
    @JsonProperty("userInfo")
    var userInfo: UserInfo,
    @JsonProperty("computeRequest")
    var computeRequest: BppComputeRequest,
    @JsonProperty("fertilizerList")
    var fertilizerList: Set<FertilizerList>
) {
    var appLanguage: String? = null
}
