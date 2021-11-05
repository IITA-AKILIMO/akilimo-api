package com.iita.akilimo.config


import javax.validation.constraints.NotBlank


class CurrencyProperties {

    @NotBlank
    var ngnUsd: Double? = null

    @NotBlank
    var tzsUsd: Double? = null

    @NotBlank
    var ghsUsd: Double? = null

}
