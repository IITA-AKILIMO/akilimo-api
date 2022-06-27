package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty

data class FertilizerList(
    @JsonProperty("name")
    var fertilizerTypeName: String,
    @JsonProperty("weight")
    var fertilizerWeight: Int = 50,
    @JsonProperty("price")
    var fertilizerCostPerBag: Double = 0.0,
    @JsonProperty("nContent")
    var nitrogenContent: String? = null,
    @JsonProperty("kContent")
    var potassiumContent: String? = null,
    @JsonProperty("pContent")
    var phosphateContent: String? = null
) {


    @JsonProperty("key")
    var fertilizerKey: String? = null

    @JsonProperty("type")
    var fertilizerType: String? = null


    @JsonProperty("available")
    var available: Boolean = false

    @JsonProperty("selected")
    var selected: Boolean = false

    @JsonProperty("cimAvailable")
    var cimAvailable: Boolean = false

    @JsonProperty("cisAvailable")
    var cisAvailable: Boolean = false
}
