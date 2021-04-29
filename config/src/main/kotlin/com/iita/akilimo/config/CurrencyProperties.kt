package com.iita.akilimo.config


import javax.validation.constraints.NotBlank


class CurrencyProperties {

    @NotBlank
    var ngnUsdRate: Double? = null

    @NotBlank
    var tzsUsdRate: Double? = null

}
