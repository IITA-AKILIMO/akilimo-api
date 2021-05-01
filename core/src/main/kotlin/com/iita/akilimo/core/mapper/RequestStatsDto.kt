package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import com.iita.akilimo.enums.EnumUserType
import java.math.BigDecimal


@JsonPropertyOrder(
    "id",
    "requestDate",
    "deviceToken",
    "countryCode",
    "lat",
    "lon",
    "fullNames",
    "genderName",
    "gender",
    "phoneNumber",
    "userType",
    "useCase",
    "createdAt",
    "updatedAt"
)
class RequestStatsDto : BaseDto() {

    @JsonProperty("id")
    var id: Long? = null

    @JsonProperty("requestDate")
    var requestDate: String? = null

    @JsonProperty("deviceToken")
    var deviceToken: String? = null

    @JsonProperty("countryCode")
    var countryCode: String? = null

    @JsonProperty("lat")
    var lat: BigDecimal? = null

    @JsonProperty("lon")
    var lon: BigDecimal? = null

    @JsonProperty("fullNames")
    var fullNames: String? = null

    @JsonProperty("genderName")
    var genderName: String? = null

    @JsonProperty("gender")
    var gender: String? = null

    @JsonProperty("phoneNumber")
    var phoneNumber: String? = null

    @JsonProperty("userType")
    var userType: EnumUserType? = null

    @JsonProperty("useCase")
    var useCase: String? = null
}
