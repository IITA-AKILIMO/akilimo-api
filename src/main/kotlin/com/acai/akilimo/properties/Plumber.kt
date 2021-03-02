package com.acai.akilimo.properties


import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank


class Plumber {
    @NotBlank
    var endpoint: String? = null
}
