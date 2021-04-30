package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonInclude
import com.fasterxml.jackson.annotation.JsonProperty

@JsonInclude(JsonInclude.Include.NON_NULL)
class SmsMessage(
    @JsonProperty("user")
    val user: String,
    @JsonProperty("password")
    val password: String,
    @JsonProperty("GSM")
    val gsm: String,
) {

    @JsonProperty("SMSText")
    var smsText: String? = null

    var useDefaultSender = false

    @JsonProperty("broadCastName")
    var broadCastName: String? = null

    @JsonProperty("token")
    var token: String? = null
    var hasMessage: Boolean = false
}
