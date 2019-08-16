package com.acai.akilimo.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import lombok.Data

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
@Data
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

    var price: String? = null

    @JsonProperty("available")
    var isAvailable: Boolean = false

    @JsonProperty("custom")
    var isCustom: Boolean = false

    var currency: String? = null

    var countryCode: String? = null

    var fertilizerCountry: String? = null

//    var createdAt: LocalDateTime? = null
//
//    var updatedAt: LocalDateTime? = null
}