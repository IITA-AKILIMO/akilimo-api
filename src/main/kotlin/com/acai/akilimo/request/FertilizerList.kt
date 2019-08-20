package com.acai.akilimo.request

import com.fasterxml.jackson.annotation.JsonProperty

class FertilizerList {

    @JsonProperty("name")
    var fertilizerTypeName: String? = null


    @JsonProperty("type")
    var fertilizerType: String? = null

    @JsonProperty("weight")
    var fertilizerWeight: Int? = null

    @JsonProperty("price")
    var fertilizerCostPerBag: String? = null


    @JsonProperty("available")
    var available: Boolean? = null

    @JsonProperty("nContent")
    var nitrogenContent: String? = null

    @JsonProperty("kContent")
    var potassiumContent: String? = null

    @JsonProperty("pContent")
    var phosphateContent: String? = null
}
