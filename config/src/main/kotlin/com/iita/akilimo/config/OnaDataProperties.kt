package com.iita.akilimo.config


import javax.validation.constraints.NotBlank


class OnaDataProperties {
    @NotBlank
    var csvPath: String? = null
}
