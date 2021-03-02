package com.acai.akilimo.mapper


import java.time.LocalDateTime


class FertilizerPriceDto {

    var recordId: Long? = null

    var priceId: Long? = null

    var minUsd: Double? = null

    var maxUsd: Double? = null

    var minLocalPrice: Double? = null

    var maxLocalPrice: Double? = null

    var minAllowedPrice: Double? = null

    var maxAllowedPrice: Double? = null

    var pricePerBag: Double? = null

    var active: Boolean? = null

    var description: String? = null

    var priceRange: String? = null

    var country: String? = null

    var fertilizerCountry: String? = null

    var createdAt: LocalDateTime? = null

    var updatedAt: LocalDateTime? = null
}
