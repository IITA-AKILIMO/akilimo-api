package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data

@Data
class UserInfo(
        val deviceID: String,
        @JsonProperty("userPhoneCC")
        var mobileCountryCode: String,
        @JsonProperty("userPhoneNr")
        var mobileNumber: String,
        @JsonProperty("fullPhoneNumber")
        var fullPhoneNumber: String,
        @JsonProperty("userName")
        var userName: String,
        @JsonProperty("userEmail")
        var userEmail: String,
        @JsonProperty("userField")
        var fieldDescription: String
) {
    @JsonProperty("SMS")
    var sendSms: Boolean = false
    @JsonProperty("email")
    var sendEmail: Boolean = false
}