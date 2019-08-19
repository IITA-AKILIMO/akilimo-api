package com.acai.akilimo.entities

import com.fasterxml.jackson.annotation.JsonProperty

class FertilizerList {

    @JsonProperty("fertilizerTypeName")
    var fertilizerTypeName: String? = null


    @JsonProperty("fertilizerType")
    var fertilizerType: String? = null

    @JsonProperty("fertilizerWeight")
    var fertilizerWeight: Int? = null

    @JsonProperty("fertilizerPrice")
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
