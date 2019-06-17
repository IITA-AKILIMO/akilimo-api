package com.acai.akilimo.properties

import lombok.Data
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank

@Data
class MessagingProperties {

    @NotBlank
    var endPoint: String? = null

    @NotBlank
    var authId: String? = null

    @NotBlank
    var authKey: String? = null

    @NotBlank
    var userName: String? = null

    @NotBlank
    var userPass: String? = null

    @NotBlank
    var sender: String? = null

    var webHookUrl: String? = null

    var testNumbers: List<String>? = null

}
