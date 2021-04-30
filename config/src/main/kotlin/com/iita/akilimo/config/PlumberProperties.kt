package com.iita.akilimo.config


import javax.validation.constraints.NotBlank


class PlumberProperties {


    @NotBlank
    var baseUrl: String? = null

    @NotBlank
    var devUrl: String? = null

    @NotBlank
    var recommendationNg: String? = null


    @NotBlank
    var recommendationTz: String? = null

    @NotBlank
    var demoMode: Boolean = false

    @NotBlank
    var recommendationNgDemo: String? = null

    @NotBlank
    var recommendationTzDemo: String? = null
}
