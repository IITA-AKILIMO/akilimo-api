package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumCountry
import io.swagger.annotations.ApiModelProperty


open class ProducePriceRequest(
        @JsonProperty("country")
        @ApiModelProperty(example = "KE", required = true)
        var country: EnumCountry,

        @ApiModelProperty(example = "1", required = true)
        var minLocalPrice: Double,

        @ApiModelProperty(example = "1", required = true)
        var maxLocalPrice: Double
) {

    var sortOrder: Int = 1 //default sort order

    var minUsd: Double = 0.0

    var maxUsd: Double = 0.0

    @ApiModelProperty(example = "false", required = false)
    var active: Boolean = true
}
