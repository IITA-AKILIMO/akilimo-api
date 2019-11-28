package com.acai.akilimo.request

import io.swagger.annotations.ApiModelProperty
import lombok.Data
import javax.validation.constraints.NotNull

@Data
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