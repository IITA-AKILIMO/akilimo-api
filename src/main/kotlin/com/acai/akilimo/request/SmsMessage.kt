package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonInclude
import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import io.swagger.annotations.ApiModelProperty

@JsonInclude(JsonInclude.Include.NON_NULL)
class SmsMessage(
        @JsonProperty("user")
        var userName: String?,
        @JsonProperty("password")
        var password: String?
) {
    @JsonProperty("GSM")
    var mobileNumber: String? = null


    @ApiModelProperty(example = "false", required = false)
    var useDefaultSender = false

    @JsonProperty("SMSText")
    var smsText: String? = null

    @JsonProperty("broadCastName")
    var broadCastName: String? = null


    @JsonProperty("token")
    var token: String? = null

    var hasMessage: Boolean = false
}
