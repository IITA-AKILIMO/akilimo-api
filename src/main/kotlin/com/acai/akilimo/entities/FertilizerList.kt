package com.acai.akilimo.entities

import com.fasterxml.jackson.annotation.JsonProperty

class FertilizerList {
    @JsonProperty("fertilizerWeight")
    var fertilizerWeight: Int? = null

    @JsonProperty("fertilizerPrice")
    var fertilizerCostPerBag: String? = null

    @JsonProperty("fertilizerType")
    var fertilizerType: String? = null

    @JsonProperty("fertilizerTypeName")
    var fertilizerTypeName: String? = null

    @JsonProperty("available")
    var available: Boolean? = null
}
