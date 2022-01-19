package com.iita.akilimo.core.request.usecases.bpp

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.core.request.FertilizerList
import com.iita.akilimo.core.request.UserInfo
import com.iita.akilimo.core.request.usecases.sp.BppComputeRequest


open class BppRequest(
    @JsonProperty("userInfo")
    var userInfo: UserInfo,
    @JsonProperty("computeRequest")
    var computeRequest: BppComputeRequest,
    @JsonProperty("fertilizerList")
    var fertilizerList: Set<FertilizerList>
) {
    var appLanguage: String? = null
}
