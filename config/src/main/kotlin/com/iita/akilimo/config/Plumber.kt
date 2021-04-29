package com.iita.akilimo.config


import javax.validation.constraints.NotBlank


class Plumber {
    @NotBlank
    var endpoint: String? = null
}
