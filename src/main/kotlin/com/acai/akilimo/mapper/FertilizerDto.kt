package com.acai.akilimo.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder


@JsonPropertyOrder(
        "fertilizerId",
        "name",
        "type",
        "weight",
        "price",
        "currency",
        "available",
        "custom",
        "N",
        "P",
        "K",
        "createdAt",
        "updatedAt"
)

class FertilizerDto {

    var fertilizerId: Long? = null

    var name: String? = null

    var type: String? = null

    //@JsonProperty("N")
    var nContent: Int? = null

    //@JsonProperty("P")
    var pContent: Int? = null

    //@JsonProperty("K")
    var kContent: Int? = null

    var weight: Int? = null

    var price: Double = 0.0

    @JsonProperty("available")
    var isAvailable: Boolean = false

    @JsonProperty("custom")
    var isCustom: Boolean = false

    var currency: String? = null

    var countryCode: String? = null

    var fertilizerCountry: String? = null

    var useCase: String? = null

//    var createdAt: LocalDateTime? = null
//
//    var updatedAt: LocalDateTime? = null
}
