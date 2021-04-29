package com.iita.akilimo.config


import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank


class CurrencyProperties {

    @NotBlank
    var ngnUsdRate: Double? = null

    @NotBlank
    var tzsUsdRate: Double? = null

}
