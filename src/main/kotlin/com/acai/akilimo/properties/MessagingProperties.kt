package com.acai.akilimo.properties

import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank

@Data
class MessagingProperties {

    @NotBlank
    var endPoint: String? = null

    @NotBlank
    var baseUrl: String? = null

    @NotBlank
    var smsUser: String? = null

    @NotBlank
    var smsToken: String? = null

    fun apiUrl(): String {
        return this.baseUrl + this.endPoint
    }
}
