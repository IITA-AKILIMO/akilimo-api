package com.iita.akilimo.core.request

import javax.validation.constraints.NotNull


open class StarchFactoryRequest {
    @NotNull
    var factoryName: String? = null
    @NotNull
    var factoryLabel: String? = null
    @NotNull
    var country: String? = null
    @NotNull
    var factoryActive: Boolean = false
}
