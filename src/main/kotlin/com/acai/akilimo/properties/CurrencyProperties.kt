package com.acai.akilimo.properties

import lombok.Data
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank

@Data
class CurrencyProperties {

    @NotBlank
    var ngnUsdRate: Double? = null

    @NotBlank
    var tzsUsdRate: Double? = null

}