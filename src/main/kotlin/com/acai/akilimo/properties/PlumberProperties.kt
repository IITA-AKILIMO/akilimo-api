package com.acai.akilimo.properties

import lombok.Data
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank

@Data
class PlumberProperties {


    @NotBlank
    var baseUrl: String? = null

    @NotBlank
    var recommendationNg: String? = null


    @NotBlank
    var recommendationTz: String? = null

    @NotBlank
    val demo: Boolean = false

    @NotBlank
    var recommendationNgDemo: String? = null

    @NotBlank
    var recommendationTzDemo: String? = null
}
