package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonPropertyOrder
import javax.validation.constraints.NotNull

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
