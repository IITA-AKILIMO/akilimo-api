package com.iita.akilimo.config


import javax.validation.constraints.NotBlank


class PlumberProperties {
    @NotBlank
    var baseUrl: String? = null

    @NotBlank
    var computeNg: String? = null

    @NotBlank
    var computeTz: String? = null

    @NotBlank
    var computeGh: String? = null
}
