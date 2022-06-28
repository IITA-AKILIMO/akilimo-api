package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonIgnore


class OperationCostDto: BaseDto() {

    var id: Long? = null

    var listIndex: Int = 0
    var operationName: String? = null
    var operationType: String? = null

    var minUsd: Double = 0.0
    var maxUsd: Double = 0.0
    var minTzs: Double = 0.0
    var maxTzs: Double = 0.0
    var minNgn: Double = 0.0
    var maxNgn: Double = 0.0

    var averageNgnPrice: Double = 0.0
    var averageTzsPrice: Double = 0.0
    var averageUsdPrice: Double = 0.0

    var priceRange: String = "We are here"

    @JsonIgnore
    fun getAverageNgn(): Double {
        return (this.minNgn + this.maxNgn) / 2
    }

    @JsonIgnore
    fun getAverageTzs(): Double {
        return (this.minTzs + this.maxTzs) / 2
    }

    @JsonIgnore
    fun getAverageUsd(): Double {
        return (this.minUsd + this.maxUsd) / 2
    }
}
