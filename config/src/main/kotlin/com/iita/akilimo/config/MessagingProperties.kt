package com.iita.akilimo.config

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
