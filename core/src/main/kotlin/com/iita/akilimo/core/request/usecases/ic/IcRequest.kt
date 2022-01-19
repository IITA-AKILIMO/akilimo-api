package com.iita.akilimo.core.request.usecases.ic

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.core.request.ComputeRequest
import com.iita.akilimo.core.request.FertilizerList
import com.iita.akilimo.core.request.UserInfo


open class IcRequest(
    @JsonProperty("userInfo")
    var userInfo: UserInfo,
    @JsonProperty("computeRequest")
    var computeRequest: IcComputeRequest,
    @JsonProperty("fertilizerList")
    var fertilizerList: Set<FertilizerList>
) {
    var appLanguage: String? = null
}
