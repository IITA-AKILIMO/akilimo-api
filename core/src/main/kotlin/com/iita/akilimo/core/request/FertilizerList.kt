package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty

class FertilizerList {

    @JsonProperty("name")
    var fertilizerTypeName: String? = null

    @JsonProperty("key")
    var fertilizerKey: String? = null

    @JsonProperty("type")
    var fertilizerType: String? = null

    @JsonProperty("weight")
    var fertilizerWeight: Int? = null

    @JsonProperty("price")
    var fertilizerCostPerBag: Double = 0.0


    @JsonProperty("available")
    var available: Boolean? = null

    @JsonProperty("selected")
    var selected: Boolean = false

    @JsonProperty("cimAvailable")
    var cimAvailable: Boolean = false

    @JsonProperty("cisAvailable")
    var cisAvailable: Boolean = false

    @JsonProperty("nContent")
    var nitrogenContent: String? = null

    @JsonProperty("kContent")
    var potassiumContent: String? = null

    @JsonProperty("pContent")
    var phosphateContent: String? = null
}
