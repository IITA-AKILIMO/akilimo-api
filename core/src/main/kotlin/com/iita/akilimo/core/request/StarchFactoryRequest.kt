package com.iita.akilimo.core.request

import io.swagger.annotations.ApiModelProperty

import javax.validation.constraints.NotNull


open class StarchFactoryRequest {
    @NotNull
    var factoryName: String? = null
    @NotNull
    var factoryLabel: String? = null
    @NotNull
    @ApiModelProperty(example = "NG", required = true)
    var country: String? = null
    @NotNull
    var factoryActive: Boolean = false
}
