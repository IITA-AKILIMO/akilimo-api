package com.acai.akilimo.request

import com.acai.akilimo.enums.EnumCountry
import com.fasterxml.jackson.annotation.JsonProperty
import io.swagger.annotations.ApiModelProperty
import lombok.Data
import java.time.LocalDateTime
import javax.validation.constraints.NotNull

//@Data
open class CassavaPriceRequest(
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