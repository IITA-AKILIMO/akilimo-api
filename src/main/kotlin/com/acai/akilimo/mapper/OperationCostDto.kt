package com.acai.akilimo.mapper

import lombok.Data
import java.time.LocalDateTime

@Data
class OperationCostDto {

    var id: Long? = null

    var operationName: String? = null
    var operationType: String? = null

    var minUsd: Double? = null
    var maxUsd: Double? = null
    var minTzs: Double? = null
    var maxTzs: Double? = null
    var minNgn: Double? = null
    var maxNgn: Double? = null
}