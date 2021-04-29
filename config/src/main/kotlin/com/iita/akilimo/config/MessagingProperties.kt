package com.iita.akilimo.config

import com.fasterxml.jackson.annotation.JsonProperty

import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank


class MessagingProperties {

    @NotBlank
    var endPoint: String? = null

    @NotBlank
    var baseUrl: String? = null

    @NotBlank
    var smsUser: String? = null

    @NotBlank
    var smsToken: String? = null

    var brandedCodes: List<String>? = null

    fun apiUrl(): String {
        return this.baseUrl + this.endPoint
    }
}
