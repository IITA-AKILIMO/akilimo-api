package com.iita.akilimo.core.request

import javax.validation.constraints.Min
import javax.validation.constraints.NotNull

open class FertilizerRequest {


    @NotNull
    var name: String? = null

    @NotNull
    var type: String? = null

    @NotNull
    var country: String? = null

    var nContent: Int? = null

    var pContent: Int? = null

    var kContent: Int? = null

    @NotNull
    @Min(value = 1, message = "Bag weight should be greater than zero")
    var weight: Int? = null

    @NotNull
    var price: String? = null

    @NotNull
    var available: Boolean = false

    @NotNull
    var custom: Boolean = false
}
