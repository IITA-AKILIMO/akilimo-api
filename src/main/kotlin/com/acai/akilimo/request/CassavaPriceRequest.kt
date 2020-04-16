package com.acai.akilimo.request

import com.acai.akilimo.enums.EnumCountry
import lombok.Data
import java.time.LocalDateTime
import javax.validation.constraints.NotNull

@Data
open class CassavaPriceRequest(

        @NotNull
        var priceId: Long,
        @NotNull
        var country: EnumCountry,
        @NotNull
        var minLocalPrice: Double,
        @NotNull
        var maxLocalPrice: Double
) {
    var minUsd: Double = 0.0

    var maxUsd: Double = 0.0

    var active: Boolean = false

    var createdAt: LocalDateTime? = null

    var updatedAt: LocalDateTime? = null
}