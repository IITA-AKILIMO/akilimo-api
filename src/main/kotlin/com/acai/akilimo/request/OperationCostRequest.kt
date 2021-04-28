package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder

import javax.validation.constraints.Min
import javax.validation.constraints.NotNull
import javax.validation.constraints.Size

@JsonPropertyOrder("minUsd", "maxUsd", "active")

open class OperationCostRequest {

    @NotNull
    var operationName: String? = null

    @NotNull
    var operationType: String? = null

    @NotNull
    var minTzs: Double? = null

    @NotNull
    var maxTzs: Double? = null

    @NotNull
    var minNgn: Double? = null

    @NotNull
    var maxNgn: Double? = null

    @NotNull
    val minUsd: Double? = null

    @NotNull
    val maxUsd: Double? = null

    @NotNull
    var active: Boolean = false
}
