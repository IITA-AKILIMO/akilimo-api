package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonPropertyOrder
import io.swagger.annotations.ApiModelProperty

import javax.validation.constraints.Min
import javax.validation.constraints.NotNull

@JsonPropertyOrder("minUsd", "maxUsd", "active")

open class FertilizerRequest {


    @NotNull
    var name: String? = null

    @NotNull
    var type: String? = null

    @NotNull
    @ApiModelProperty(example = "NG", required = true)
    var country: String? = null

    var nContent: Int? = null

    var pContent: Int? = null

    var kContent: Int? = null

    @NotNull
    @Min(value = 1, message = "Bag weight should be greater than zero")
    var weight: Int? = null

    @NotNull
    @ApiModelProperty(example = "NA", required = true)
    var price: String? = null

    @NotNull
    var available: Boolean = false

    @NotNull
    var custom: Boolean = false
}