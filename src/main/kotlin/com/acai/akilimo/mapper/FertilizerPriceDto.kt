package com.acai.akilimo.mapper

import lombok.Data
import java.time.LocalDateTime

@Data
class FertilizerPriceDto {

    var id: Long? = null

    var priceId: Long? = null

    var minUsd: Double? = null

    var maxUsd: Double? = null

    var pricePerBag: Double? = null

    var active: Boolean? = null

    var description: String? = null

    var priceRange: String? = null

    var country: String? = null

    var fertilizerCountry: String? = null

    var createdAt: LocalDateTime? = null

    var updatedAt: LocalDateTime? = null
}